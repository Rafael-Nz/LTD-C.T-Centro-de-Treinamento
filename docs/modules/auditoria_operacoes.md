# Operação da auditoria

## Objetivo

Documenta a instalação, a manutenção e a verificação da `hash chain` do [Módulo Auditoria](auditoria.md). Os procedimentos se destinam ao administrador do ambiente e utilizam acesso ao banco e ao terminal.

O [catálogo de eventos](auditoria_eventos.md) descreve a interpretação dos registros de negócio e de requisição.

## Diretório

- [`docs/sql/auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql)
- [`docs/sql/auditoria_permissoes.sql`](../sql/auditoria_permissoes.sql)
- [`docs/sql/setup/banco.sql`](../sql/setup/banco.sql)
- [`docs/sql/auditoria_leitura.sql`](../sql/auditoria_leitura.sql)
- [`api/core/Audit/AuditIntegrity.php`](../../api/core/Audit/AuditIntegrity.php)
- [`tools/sistema/verify_audit.php`](../../tools/sistema/verify_audit.php)

## Acesso e dependências

A captura depende dos triggers instalados e de tabelas InnoDB. O formato utiliza JSON, `SHA2(..., 256)`, `UTC_TIMESTAMP(6)` e transactional locks.

O verificador utiliza PHP CLI, PDO MySQL, o autoload do Composer e o arquivo `.env` na raiz do projeto. As configurações de conexão são carregadas por [`Database.php`](../../api/core/Database/Database.php):

| Variável | Finalidade |
| --- | --- |
| `DB_HOST` | Servidor do banco. |
| `DB_PORT` | Porta da conexão. |
| `DB_DATABASE` | Banco da aplicação. |
| `DB_USERNAME` | Usuário de conexão. |
| `DB_PASSWORD` | Senha de conexão. |

O utilitário não inicia o roteamento HTTP. A configuração Apache bloqueia `/tools` com 403; se o script for alcançado diretamente por outra configuração de servidor, o guard PHP recusa execução não CLI com 404. Os comandos deste documento são executados a partir da raiz do projeto.

A instalação exige privilégios para criar tabelas e triggers. A conta da aplicação é `ctt_app`, com grants por tabela, sem privilégios administrativos. Em `audit_logs`, o INSERT é concedido somente nas colunas do envelope HTTP; ID, hashes, horário e snapshots não podem ser informados diretamente pela aplicação. A conta definida como `DEFINER` dos triggers possui os privilégios necessários para capturar os snapshots e atualizar o `chain head`. Instale os triggers com uma conta administrativa separada, nunca com `ctt_app`.

### Camadas de proteção

| Camada | Proteção implementada |
| --- | --- |
| Least privilege | `ctt_app` tem DML nas tabelas de negócio; SELECT e INSERT limitado no log; somente SELECT no chain head. |
| Sem DDL | A conta não recebe DROP, ALTER, TRIGGER, FILE ou GRANT OPTION. Não pode usar TRUNCATE nem remover as proteções. |
| Triggers append-only | UPDATE/DELETE dos logs também são recusados por SIGNAL, independentemente da verificação dos hashes. |
| Proteção HTTP | `.htaccess` bloqueia arquivos ocultos, backups, SQLs e os diretórios docs/tools/vendor; directory listing está desabilitado. |
| Guard de configuração | Fora de `APP_ENV=development`, `Database` recusa username root ou senha vazia. O padrão de username é ctt_app. |
| Hash chain e checkpoint | Permitem verificar alterações e reescritas contra uma referência externa confiável. |

As regras Apache devem permanecer habilitadas no servidor. Em outro servidor web, configure bloqueios equivalentes. Os arquivos PHP continuam podendo carregar `.env` e dependências pelo filesystem; os bloqueios são de acesso HTTP.

Os grants reforçam os triggers: [TRUNCATE exige DROP, e a manipulação de triggers exige TRIGGER no MariaDB](https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements/grant). A flag [F do mod_rewrite retorna 403](https://httpd.apache.org/docs/2.4/rewrite/flags.html#flag_f).

### Provisionar a conta da aplicação

Depois de instalar o schema e os triggers com a conta administrativa:

1. Crie uma conta **exclusiva e sem roles herdadas**. Substitua o marcador por uma senha forte gerada para o ambiente; não use uma senha de exemplo:

   ```sql
   CREATE USER 'ctt_app'@'localhost' IDENTIFIED BY '<senha-forte-gerada-no-ambiente>';
   ```

2. Execute `docs/sql/auditoria_permissoes.sql` como administrador. O script revoga os grants anteriores da conta e aplica somente as permissões explícitas; não o utilize com uma conta compartilhada com outros sistemas.
3. Configure `DB_USERNAME=ctt_app` e a senha correspondente em `.env`. A senha não deve ser versionada nem exposta no navegador.
4. Execute `php tools/sistema/verify_audit.php` usando essa configuração.

O SQL usa `db_centro_treinamento` e a conta `ctt_app@localhost`. Ajuste banco/host explicitamente em outro ambiente. Uma instalação completa que recrie tabelas deve reaplicar os grants. Novas tabelas e novas colunas de envelope não recebem privilégios automaticamente. Não acrescente um GRANT amplo sobre `banco.*`, pois ele eliminaria a separação entre dados de negócio e auditoria.

No ambiente local, a conta restrita foi provisionada com senha aleatória de 256 bits em `.env`. A conta administrativa permanece separada para manutenção; a aplicação não usa mais root.

## Instalação

### Banco com tabelas de negócio existentes

1. Suspenda requisições e escritas concorrentes durante a instalação.
2. Selecione o banco da aplicação e execute [`auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql).
3. Execute a verificação pelo terminal:

```powershell
php tools/sistema/verify_audit.php
```

O arquivo SQL contém `DELIMITER`; utilize um cliente que interprete essa diretiva ao criar os triggers.

A instalação cria `audit_logs`, `audit_chain_state` e os triggers de captura/integridade. Reaplicá-la sobre a mesma versão preserva os registros e substitui os triggers. Não há conversão automática de estruturas antigas por `CREATE TABLE IF NOT EXISTS`.

DDL faz commit implícito. A substituição dos triggers ocorre por instruções separadas, por isso a instalação não deve concorrer com escritas.

### Instalação completa do sistema

[`docs/sql/setup/banco.sql`](../sql/setup/banco.sql) já inclui as tabelas e os triggers da auditoria. Esse arquivo recria o banco inteiro; não o utilize para acrescentar apenas auditoria a um banco com dados.

## Componentes

### Triggers de negócio

Os triggers `audit_<tabela>_insert`, `audit_<tabela>_update` e `audit_<tabela>_delete` inserem uma linha por operação coberta. Os campos e nomes emitidos estão no [catálogo](auditoria_eventos.md).

Triggers `audit_<pai>_cascade` capturam efeitos de exclusão de filhos e de SET NULL antes da exclusão do pai. A manutenção dos relacionamentos exige revisar também esses triggers.

### Trigger de integridade

`audit_logs_chain_insert` é executado BEFORE INSERT sobre `audit_logs`:

1. Lê e bloqueia `audit_chain_state.id = 1` com SELECT FOR UPDATE.
2. Converte `action` e `operation` para minúsculas.
3. Atribui o próximo ID, `hash_version = 1`, `chain_id` e `previous_hash`.
4. Define `created_at = UTC_TIMESTAMP(6)`.
5. Completa os metadados ausentes e calcula `row_hash`.
6. Atualiza `last_id` e `last_hash` no `chain head`.

A linha de auditoria e a atualização do `chain head` participam da mesma transação da escrita. Se o `chain head` estiver ausente ou o hash não puder ser calculado, o trigger sinaliza erro.

### Proteção de escrita

`audit_logs_no_update` e `audit_logs_no_delete` rejeitam UPDATE/DELETE por SIGNAL. A rejeição da instrução não cria um evento persistente de tentativa de adulteração, pois a própria transação é desfeita.

### Verificador CLI

[`verify_audit.php`](../../tools/sistema/verify_audit.php) carrega a configuração, chama `AuditIntegrity::verify()` e apresenta o resultado. Pode ler uma referência anterior e exportar um novo checkpoint.

Sua execução é manual. Não há tarefa agendada nem armazenamento externo configurado pelo módulo.

## Contexto da conexão

`Audit::begin()` inicializa:

```text
@audit_user_id
@audit_user_name
@audit_ip
@audit_user_agent
@audit_request_id
@audit_correlation_id
@audit_http_method
@audit_route
@audit_context_data
```

Os triggers de negócio copiam ator, IP, user agent e request_id. O trigger de integridade completa correlation_id, método, rota e contexto ausentes por COALESCE.

Se não houver request_id no registro ou na conexão, o banco gera um UUID sem hífens; correlation_id usa o contexto disponível ou esse request_id. Em SQL direto sem contexto, cada evento pode receber seu próprio ID, com ator/IP nulos. Para agrupar várias escritas de um script, o contexto deve ser definido explicitamente naquela conexão.

A API usa conexões não persistentes e redefine as variáveis no início da requisição. O acréscimo de response_id no contexto PHP não reescreve as variáveis nem os eventos já gravados.

## Regras de transação

- A sequência global usa `last_id + 1`; não depende de AUTO_INCREMENT.
- O `chain head` permanece bloqueado até o término da transação.
- Rollback reverte as linhas de auditoria e o `chain head`, evitando lacunas decorrentes de tentativas desfeitas.
- A gravação da auditoria também é atômica com uma instrução em autocommit.
- O serviço base adquire o lock do `chain head` antes das escritas de negócio. Chamadas internas usam savepoints.
- Transações longas aumentam a espera das demais escritas auditadas.
- SQL externo que adquire locks em outra ordem pode sofrer deadlock. Não há retry automático de transação implementado na auditoria.
- A atomicidade de cada escrita não transforma várias escritas independentes em uma transação única.

O evento `transaction_committed` pertence à integração do serviço base. A ausência desse evento não invalida eventos de negócio confirmados em autocommit.

## Formato da `hash chain` SHA-256

### Identidade e encadeamento

O `chain head` guarda `chain_id`, `last_id` e `last_hash`. A `hash chain` vazia possui last_id zero e last_hash com 64 zeros.

Cada registro contém:

| Campo | Valor |
| --- | --- |
| `hash_version` | 1. |
| `chain_id` | Identidade da `hash chain`, compartilhada com o `chain head`. |
| `previous_hash` | Hash do registro anterior; 64 zeros no primeiro. |
| `row_hash` | SHA-256 hexadecimal do payload da linha. |

O `chain head` permite detectar também a exclusão do último registro, desde que não tenha sido adulterado junto.

### Serialização v1

A ordem é definida em `AuditIntegrity::FIELDS` e deve corresponder à expressão SQL:

```text
hash_version, chain_id, id, previous_hash, user_id, user_name,
action, operation, module, entity_type, entity_id, old_data, new_data,
ip_address, user_agent, request_id, correlation_id, http_method, route,
context_data, result, http_status, created_at
```

Com `H` representando SHA-256 em hexadecimal minúsculo:

```text
encode(NULL) = "N"
encode(v)    = "S" || H(bytes_da_representacao_textual_armazenada(v))
payload      = "audit-v1|" || encode(campo_1) || ... || encode(campo_23)
row_hash     = H(payload)
```

O SQL usa `SHA2(CAST(valor AS BINARY), 256)`; o PHP usa `hash('sha256', (string) $valor)`. O próprio row_hash não integra o payload.

JSON é verificado na representação textual armazenada. Datas usam `YYYY-MM-DD HH:MM:SS.ffffff` em UTC. Não reordene propriedades JSON, não normalize textos e não converta o fuso antes de verificar. NULL é diferente de string vazia.

Alterar o nome de uma action, a representação de um valor ou a ordem dos campos altera o hash. Mudanças de formato precisam de uma migração explícita que defina como verificar o histórico anterior.

## Comandos principais

### Verificar o histórico

```powershell
php tools/sistema/verify_audit.php
```

Retorna código de saída 0 em sucesso e 1 em falha. A saída de sucesso informa a quantidade de registros e apresenta `chain_id`, `last_id` e `last_hash`.

O verificador:

- exige conexão sem transação aberta;
- inicia um snapshot em REPEATABLE READ;
- lê o `chain head` e pagina as linhas pela PK em lotes de até 1.000 registros;
- confere sequência contígua desde 1, versão, chain_id, vínculo anterior e hash recalculado;
- compara a última linha com o `chain head`;
- valida o checkpoint, quando fornecido.

A execução tem custo O(n) registros e memória limitada ao lote e seus dados. Commits posteriores ao snapshot não entram naquela verificação.

### Criar checkpoint

```powershell
php tools/sistema/verify_audit.php --checkpoint=caminho/ancora-001.json
```

O arquivo contém somente a identidade e a posição/hash verificados. O diretório deve existir. Um arquivo já existente não é sobrescrito.

Guarde o checkpoint fora do banco, em local protegido contra escrita por quem controla a aplicação e o banco.

### Verificar contra checkpoint

```powershell
php tools/sistema/verify_audit.php --anchor=caminho/ancora-001.json
```

Novos eventos posteriores ao ponto ancorado são aceitos se a `hash chain` continuar válida. Uma identidade de `hash chain` diferente, um hash divergente naquela posição ou a ausência da posição provocam falha.

É possível validar a referência anterior antes de exportar outra:

```powershell
php tools/sistema/verify_audit.php --anchor=caminho/ancora-001.json --checkpoint=caminho/ancora-002.json
```

## Consulta dos registros

Execute [`auditoria_leitura.sql`](../sql/auditoria_leitura.sql) no banco selecionado para consultar contexto, dados anteriores/posteriores e hashes, ordenados pelo ID da `hash chain`.

A consulta não verifica os hashes. Use o CLI para a verificação criptográfica e o [catálogo de eventos](auditoria_eventos.md) para interpretar ações e reconstruir sequências por request_id.

## Erros e diagnóstico

| Mensagem ou sinal | Interpretação |
| --- | --- |
| API responde 503 por auditoria indisponível | Falha no início da captura; os middlewares/controlador não são executados. |
| `Cabeca da cadeia ausente.` | O verificador não encontrou o estado id = 1. |
| `Integridade violada no registro #...` | Primeira falha observada de sequência, versão, identidade, vínculo ou conteúdo. |
| `Checkpoint invalido ou cadeia substituida.` | Referência com formato inválido ou chain_id divergente. |
| `Checkpoint divergente no registro #...` | Hash diferente no ponto externo ancorado. |
| `Final da cadeia ausente, truncado ou divergente do checkpoint.` | Chain head divergente do final lido ou posição ancorada não encontrada. |
| `Nao foi possivel consultar a auditoria.` | O CLI recebeu uma exceção PDO e ocultou seus detalhes. |
| Início sem resultado final | Pode indicar execução em andamento, interrupção do processo ou falha de finalização. |

O verificador identifica divergências, não o responsável pela adulteração. Ele não modifica a `hash chain` nem insere um evento de detecção.

Durante a captura, os fallbacks `auditoria_indisponivel`, `requisicao_finalizada_com_transacao_aberta` e `falha_ao_registrar_resultado` podem aparecer no log PHP com request_id. Esses registros de diagnóstico não pertencem à `hash chain` SHA-256; suas condições estão no catálogo.

## Limites de integridade

Hashes sem segredo não constituem assinatura digital. Quem controla os dados, os triggers e o `chain head` pode recalcular a `hash chain` inteira. Um checkpoint externo confiável detecta a reescrita do trecho já ancorado ou a substituição/truncamento da `hash chain`.

Essa referência não protege retroativamente o período posterior ao último checkpoint e deixa de ser confiável se o atacante também puder substituir o arquivo externo.

Os triggers não impedem DDL, como DROP/TRUNCATE, nem alterações administrativas nas próprias proteções. Tentativas SQL rejeitadas não são registradas de forma durável por esse mecanismo. Não há captura das cascatas de UPDATE de chaves primárias, de novas tabelas não cobertas ou de novos campos fora da lista.

Essas operações administrativas são negadas à conta da aplicação pelos grants. Um administrador do banco/servidor ainda pode conceder privilégios ou remover as proteções. Uma aplicação comprometida pode executar as operações de negócio que sua conta permite e inserir envelopes de eventos; isso não lhe dá permissão para reescrever eventos antigos, fornecer snapshots/hashes diretamente ou modificar o chain head. Não há promessa de impedir um administrador com controle total do ambiente.

## Manutenção

O SQL é mantido diretamente em [`auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql). Ao alterar tabelas, campos, relacionamentos ou eventos:

1. Revise os triggers de linha e os de cascata afetados.
2. Atualize o bloco `BEGIN AUDIT OPERATIONS` de [`banco.sql`](../sql/setup/banco.sql).
3. Atualize o [catálogo de eventos](auditoria_eventos.md).
4. Revise `auditoria_permissoes.sql` se novas tabelas ou colunas de envelope forem necessárias; mantenha os privilégios da auditoria separados.
5. Caso mude a serialização, mantenha o cálculo SQL e `AuditIntegrity` compatíveis com a versão do histórico.
6. Aplique a mudança sem escritas concorrentes e execute a verificação.

Não há expurgo automático. Remover registros ou renomear actions em uma `hash chain` existente exige uma estratégia de preservação/verificação; excluir linhas diretamente rompe o histórico.

## Relação com a documentação do módulo

- [Módulo Auditoria](auditoria.md): objetivo, acesso, dados, componentes e regras.
- [Eventos da auditoria](auditoria_eventos.md): nomes emitidos, condições, snapshots e consultas.
