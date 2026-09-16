# Módulo Auditoria

## Objetivo

Registra operações do sistema para identificar quem executou uma ação, quando ela ocorreu, qual registro foi afetado, de onde veio a requisição e quais dados mudaram.

Os eventos de requisição e de negócio são armazenados em `audit_logs`, na mesma `hash chain` SHA-256. O módulo permite investigar alterações de cadastro, movimentações financeiras, autenticação e permissões, além de verificar mudanças indevidas no próprio histórico.

A auditoria é uma funcionalidade transversal do núcleo da API. Sua captura ocorre de forma síncrona, na conexão usada pela operação.

## Terminologia técnica

A documentação mantém os termos técnicos em inglês para corresponder ao código e aos conceitos do banco. As explicações permanecem em português.

| Termo | Contexto no módulo |
| --- | --- |
| `DEFINER` | Conta usada como contexto de privilégios na execução do trigger. |
| `hash chain` | Encadeamento dos hashes dos registros de auditoria. |
| `chain head` | Estado persistido em `audit_chain_state`, com `chain_id`, `last_id` e `last_hash`. |
| `row snapshot` | Valores de uma linha antes ou depois da operação, em `old_data` e `new_data`. |
| `consistent snapshot` | Visão consistente do banco usada pelo verificador em REPEATABLE READ; não é o mesmo que um row snapshot. |
| `lock` | Bloqueio transacional, como o obtido por SELECT FOR UPDATE no chain head. |
| `commit`, `rollback`, `savepoint` | Operações de confirmação, reversão e delimitação interna de transações. |
| `checkpoint` | Referência externa com a identidade, posição e hash verificados da hash chain. |
| `trigger`, `payload`, `fallback` | Termos mantidos como usados no código e na operação do módulo. |

Identificadores, nomes de arquivos, exemplos SQL e mensagens literais de erro são reproduzidos como existem na implementação, mesmo quando uma mensagem do código está em português.

## Diretório

- [`api/core/Audit`](../../api/core/Audit)
- [`docs/sql/auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql)
- [`docs/sql/auditoria_permissoes.sql`](../sql/auditoria_permissoes.sql)
- [`docs/sql/auditoria_leitura.sql`](../sql/auditoria_leitura.sql)
- [`tools/sistema/verify_audit.php`](../../tools/sistema/verify_audit.php)

## Acesso

O contexto da requisição usa o usuário autenticado na sessão. Tentativas anônimas também podem gerar eventos, com `user_id` e `user_name` nulos.

O início da auditoria ocorre antes dos middlewares da rota. Isso permite registrar tentativas bloqueadas por autenticação, autorização ou CSRF. A geração de um evento inicial não significa que a requisição foi autorizada.

A consulta direta ao banco e a execução do verificador dependem do acesso administrativo ao ambiente. Não existe uma permissão de API específica para consultar os logs, pois o módulo não expõe essa funcionalidade por HTTP.

## Rotas principais

O módulo não possui rotas próprias, Controller, Repository ou DTO. Ele participa do roteamento e das transações dos outros módulos por meio das classes do núcleo e dos triggers do banco.

| Ponto de entrada | Função |
| --- | --- |
| `Router::dispatch()` | Inicia o contexto de auditoria quando encontra uma rota. |
| `Controller::json()` | Encaminha o ID retornado, quando aplicável, para o evento final. |
| `Service::transaction()` | Coordena o lock do `chain head` e o marcador de commit externo. |
| Triggers das tabelas cobertas | Registram as alterações de negócio, inclusive SQL direto. |
| `php tools/sistema/verify_audit.php` | Verifica a integridade do histórico via CLI. |

## Fluxo de registro

1. O roteador encontra a rota e chama `Audit::begin()`.
2. A auditoria cria os IDs de correlação, captura o ator e a origem e configura o contexto da conexão.
3. Em métodos diferentes de GET/HEAD, registra `request_started` antes dos middlewares.
4. As escritas nas tabelas cobertas disparam eventos com os dados anteriores e posteriores.
5. Uma transação externa gerenciada por `Service` insere `transaction_committed` antes do commit.
6. Ao finalizar a requisição, a auditoria registra o resultado HTTP, quando aplicável.

GET/HEAD bem-sucedidos não geram início nem resultado final. Se uma dessas requisições alterar uma tabela coberta, o trigger ainda registra a escrita. GET/HEAD com erro pode gerar somente `operation_failed`.

OPTIONS tratado no bootstrap e requisições sem rota correspondente não entram nesse fluxo.

## Dados registrados

| Coluna | Tipo no banco | Restrição/semântica |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED, PK | Sequência global atribuída pelo trigger; não usa AUTO_INCREMENT |
| `hash_version` | TINYINT UNSIGNED NOT NULL | Forçado a 1 pelo trigger |
| `chain_id` | CHAR(32) NOT NULL | Identidade da `hash chain` criada por UUID sem hífens; não é segredo |
| `previous_hash`, `row_hash` | CHAR(64) NOT NULL | SHA-256 hexadecimal; previous_hash da primeira linha é composto de zeros |
| `user_id` | BIGINT UNSIGNED NULL | Identidade histórica do ator; sem FK |
| `user_name` | VARCHAR(201) NULL | Nome capturado para aquele contexto |
| `action` | VARCHAR(50) NOT NULL | Identificador em inglês e minúsculo; não é ENUM |
| `operation` | VARCHAR(10) NOT NULL | SQL, método HTTP ou `auth`, sempre em minúsculas |
| `module` | VARCHAR(100) NOT NULL | Classificação do emissor; consulte o catálogo |
| `entity_type` | VARCHAR(100) NULL | Tabela nos eventos de negócio |
| `entity_id` | BIGINT UNSIGNED NULL | Chave principal ou parte da chave composta |
| `old_data`, `new_data`, `context_data` | JSON NULL | Row snapshots dos campos permitidos e metadados de correlação |
| `ip_address` | VARCHAR(45) NULL | IPv4/IPv6 validado |
| `user_agent` | VARCHAR(512) NULL | Informação declarada pelo cliente; não é prova de identidade |
| `request_id`, `correlation_id` | CHAR(32) NULL no DDL | Normalmente preenchidos pela API; o trigger fornece fallback se NULL |
| `http_method` | VARCHAR(10) NULL | Método da requisição |
| `route` | VARCHAR(255) NULL | Modelo da rota, não a URL inteira |
| `result` | VARCHAR(20) NOT NULL DEFAULT 'success' | Convenção dos emissores, não ENUM |
| `http_status` | SMALLINT UNSIGNED NULL | Status dos eventos finais HTTP |
| `created_at` | DATETIME(6) NOT NULL | Sobrescrito por UTC_TIMESTAMP(6) no trigger |

`audit_chain_state` possui somente a linha `id = 1`, com `chain_id`, `last_id` e `last_hash`. O CHECK restringe o ID do estado a 1. Ambas as tabelas usam InnoDB. `audit_logs` usa utf8mb4/utf8mb4_unicode_ci.

Índices: `(user_id, created_at)`, `(action, created_at)`, `(module, created_at)`, `(entity_type, entity_id, created_at)`, `created_at`, `request_id` e `correlation_id`. A verificação criptográfica percorre a PK `id`; propriedades internas de JSON não possuem índice dedicado.

### Contexto da requisição

| Campo | Origem e comportamento |
| --- | --- |
| `request_id` | `bin2hex(random_bytes(16))`: 128 bits aleatórios gerados pelo servidor. Retornado em `X-Request-ID`. |
| `correlation_id` | Igual ao request_id no fluxo atual. Retornado em `X-Correlation-ID`. Não é aceito de um cabeçalho enviado pelo cliente. |
| `user_id` | `$_SESSION['user_id']`, quando numérico e positivo. Não é lido do corpo da requisição. |
| `user_name` | Nome e sobrenome consultados em `usuario`. Um ID sem cadastro usa `Usuario nao encontrado`; sem ator, o nome é NULL. |
| `ip_address` | `REMOTE_ADDR` validado para IPv4/IPv6. Não utiliza `X-Forwarded-For`. |
| `user_agent` | Valor informado pelo cliente, limitado a 512 caracteres. |
| `http_method` | Método HTTP na grafia recebida pelo roteador, como `POST`. |
| `route` | Modelo da rota, como `/alunos/{id}`, sem incorporar o conteúdo de parâmetros textuais da URL. |
| `context_data.params` | Parâmetros da rota compostos apenas por dígitos e com até dez caracteres, convertidos em inteiros. |
| `context_data.response_id` | Acrescentado ao contexto PHP quando a resposta possui `id` numérico no primeiro nível e status < 400. |

Parâmetros textuais, query string e corpo da requisição não são copiados. Quando não há parâmetros permitidos, a serialização pode produzir `"params": []`.

O login bem-sucedido atualiza a identidade do evento final com a sessão autenticada. O logout mantém a identidade capturada antes de encerrar a sessão. Eventos anteriores não são reescritos.

### Nomenclatura e resultado

`action` usa identificadores em inglês e minúsculas, no formato snake_case: `student_updated`, `invoice_paid` e `password_changed`.

`operation` também usa minúsculas: `insert`, `update`, `delete`, `auth` ou o método HTTP aplicável, como `post`. O trigger aplica `LOWER()` aos dois campos antes de calcular o hash. Essa normalização não traduz nomes arbitrários.

| Resultado | Uso |
| --- | --- |
| `pending` | Início de requisição ainda sem resultado. |
| `success` | Escrita de negócio, marcador transacional ou resposta HTTP sem erro, conforme o evento. |
| `failure` | Finalização HTTP com status >= 400. |

`http_status` é preenchido nos eventos finais HTTP. Nos eventos de negócio ele permanece NULL.

## Eventos principais

| Evento | Significado |
| --- | --- |
| `request_started` | Requisição admitida no fluxo auditado, antes dos middlewares. |
| `request_completed` | Finalização com status < 400 em rota sem evento final específico. |
| `operation_failed` | Resultado HTTP de erro em rota sem evento final específico. |
| `transaction_committed` | Marcador que persiste junto com o commit externo de um serviço. |
| `login` / `login_failed` | Resultado da rota de login. |
| `logout` / `logout_failed` | Resultado da rota de logout. |
| `password_changed` | Alteração da senha armazenada, sem copiar a senha ou seu hash. |
| `student_updated` | Alteração de campos próprios do aluno ou de seus dados comuns de usuário; a tabela é identificada por entity_type. |
| `payment_created` | Inserção de pagamento. |
| `invoice_paid` | Transição de uma cobrança para o estado paga. |
| `role_changed` / `permission_changed` | Alteração de perfil/cargo atribuído ou de vínculo de permissão. |

O [catálogo de eventos](auditoria_eventos.md) contém todos os nomes, campos permitidos, condições de geração, prioridades e exemplos de sequências.

## Componentes

### Audit

Arquivo: [`Audit.php`](../../api/core/Audit/Audit.php)

Responsável por:

- criar o contexto da requisição e os cabeçalhos de correlação;
- capturar o ator e configurar as variáveis da conexão;
- emitir os eventos de início, transação e finalização;
- preservar a identidade no logout e atualizar o ator no resultado do login;
- encaminhar falhas de captura para o log de erro PHP.

### Triggers de auditoria

Arquivo: [`auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql)

Responsáveis por:

- registrar INSERT/UPDATE/DELETE nas tabelas cobertas;
- selecionar os campos permitidos de antes/depois;
- classificar alterações de status, perfil, permissão e senha;
- capturar efeitos de exclusões em cascata e SET NULL;
- atribuir sequência, horário UTC e hashes;
- bloquear UPDATE/DELETE dos próprios logs.

### AuditIntegrity

Arquivo: [`AuditIntegrity.php`](../../api/core/Audit/AuditIntegrity.php)

Responsável por:

- definir a ordem e a codificação dos campos do formato v1;
- recalcular os hashes a partir dos valores armazenados;
- conferir sequência, encadeamento e estado final em um `consistent snapshot`;
- comparar o histórico com um checkpoint externo opcional.

### Integração com o núcleo HTTP e os serviços

Arquivos: [`Router.php`](../../api/core/Http/Router.php), [`Controller.php`](../../api/core/Http/Controller.php) e [`Service.php`](../../api/core/Services/Service.php).

O roteador interrompe a execução com 503 se o início da auditoria falhar. O controlador comunica o ID da resposta. O serviço coordena transações externas e savepoints, adquirindo o lock do `chain head` antes das escritas de negócio.

## Regras de negócio e privacidade

- Eventos são inseridos; um início com resultado pending não é atualizado para success/failure.
- Snapshots contêm somente os campos explicitamente definidos nos triggers.
- INSERT tem antes nulo; DELETE tem depois nulo; UPDATE contém os dois estados.
- UPDATE sem diferença nos campos permitidos não gera evento-base. A comparação da senha é independente.
- Uma operação em lote pode gerar vários eventos. Rotinas que excluem e recriam linhas registram ambas as operações.
- Alterações e seus eventos participam da mesma transação InnoDB. Rollback também desfaz o avanço da `hash chain`.
- `transaction_committed` não é emitido por toda chamada arbitrária de commit nem por autocommit.
- Resultado HTTP sem erro não prova que houve mudança. Erro HTTP não prova rollback de todas as escritas da requisição.
- Senhas, hashes de senha, tokens, cookies, autorização, CPF, nascimento e dados clínicos não entram nos snapshots.
- Nome, email, endereço, contato e valores financeiros presentes na lista permitida exigem acesso restrito aos logs.
- A `hash chain` detecta divergências, mas não impede que um administrador com controle total reconstrua o histórico. Checkpoints externos protegem a referência já ancorada.
- Não há limpeza automática, fila de reenvio ou verificação agendada configurada.

## Proteções de acesso

A aplicação utiliza a conta restrita `ctt_app`. Ela pode operar os cadastros e inserir o envelope HTTP dos eventos, mas não pode alterar/excluir logs, fornecer hashes ou snapshots diretamente, modificar o `chain head`, executar DDL ou remover triggers. Os triggers executam com um `DEFINER` administrativo separado.

O Apache bloqueia o acesso HTTP a `.env`, arquivos ocultos, SQLs, backups e aos diretórios docs/tools/vendor. Em produção, o núcleo de conexão recusa configuração root ou senha vazia. Os procedimentos de grants e os limites dessas proteções estão em [Operação da auditoria](auditoria_operacoes.md).

## Relação com outros módulos

| Módulos | Informações auditadas |
| --- | --- |
| Usuário, aluno e funcionário | Dados permitidos de cadastro, contatos, endereços e status. |
| Autenticação | Login, logout, tentativas malsucedidas e troca de senha. |
| Cargo e permissões | Cadastro de cargos/permissões e alterações de atribuição. |
| Financeiro | Serviços, planos, contratos, cobranças, pagamentos e respectivos itens. |
| Turma e treino | Vínculos aluno/turma, horários, agenda e presenças. |
| Modalidade e local | Cadastros e alterações operacionais. |
| Anamnese e avaliação | Eventos de requisição, sem snapshots de dados clínicos. |

## Documentação complementar

- [Eventos da auditoria](auditoria_eventos.md): catálogo completo, snapshots e consultas de investigação.
- [Operação da auditoria](auditoria_operacoes.md): instalação, `hash chain` SHA-256, verificação, checkpoints e diagnóstico.
