# Eventos da auditoria

## Objetivo

Detalha os eventos registrados pelo [Módulo Auditoria](auditoria.md), com condições de emissão, entidades afetadas, conteúdo dos snapshots e exemplos de investigação.

O documento complementa a visão geral do módulo. Os procedimentos de instalação, verificação e checkpoint estão em [Operação da auditoria](auditoria_operacoes.md).

## Diretório

- [`api/core/Audit/Audit.php`](../../api/core/Audit/Audit.php)
- [`api/core/Services/Service.php`](../../api/core/Services/Service.php)
- [`docs/sql/auditoria_operacoes.sql`](../sql/auditoria_operacoes.sql)
- [`docs/sql/auditoria_leitura.sql`](../sql/auditoria_leitura.sql)

## Padrão dos eventos

Os emissores usam nomes em inglês e minúsculos (snake_case). A coluna action é VARCHAR, não ENUM; a collation do banco não impõe comparação sensível a caixa. O trigger normaliza action e operation antes de calcular o hash.

## Estrutura do evento

Uma linha descreve um fato observado por um produtor. Há três produtores:

| Produtor | Fato observado | Momento | Snapshots |
| --- | --- | --- | --- |
| PHP: `Audit::begin/finish/transaction` | Início, resultado HTTP ou transação do serviço | Roteamento, finalização ou imediatamente antes do commit externo | `old_data = NULL`, `new_data = NULL` |
| Triggers de negócio | INSERT, UPDATE ou DELETE de uma linha coberta | AFTER da escrita direta; BEFORE da exclusão do pai para efeitos de FK | Campos explicitamente permitidos, provenientes de OLD/NEW ou SELECT dos filhos |
| Script de carga de desenvolvimento | Aplicação do seed | Dentro da transação de carga | Ambas nulas |

Leia sempre a tupla `(action, operation, module, entity_type, entity_id)`. O nome do evento sozinho não identifica a tabela e não descreve todos os campos alterados.

- `action` classifica o fato: por exemplo, `invoice_paid`.
- `operation` é a operação SQL nos triggers (`insert`, `update`, `delete`); nos eventos PHP é o método HTTP em minúsculas ou `auth`. Portanto, `transaction_committed` pode ter `operation = post`; não há um valor `commit` emitido por esse produtor. Os termos INSERT/UPDATE/DELETE usados nas tabelas deste documento designam as instruções SQL; os valores persistidos em `operation` são minúsculos.
- `module` dos triggers vem do mapa fixo abaixo. No PHP vem do primeiro segmento da rota, exceto `/auth/*`, que usa `autenticacao`.
- `entity_type` é o nome da tabela nos triggers, não o nome de uma classe PHP.
- `entity_id` é a chave indicada no mapa. Para chaves compostas, consulte também os snapshots.
- `result` não é o estado do cadastro: uma cobrança cancelada possui `new_data.status = "cancelada"` e `result = "success"`, porque o cancelamento foi persistido.

### Envelope por produtor

| Campo | Eventos PHP | Eventos dos triggers |
| --- | --- | --- |
| `user_id`, `user_name` | Contexto capturado da sessão; login bem-sucedido atualiza o ator | Variáveis da conexão inicializadas no início da requisição |
| `entity_type` | `usuario` nas rotas `/auth/*`; NULL nas demais | Tabela afetada |
| `entity_id` | ID do ator em rota auth com `result = success`; NULL nos demais casos | Chave da linha afetada, mesmo se o ator for anônimo |
| `http_status` | Preenchido somente no evento final | NULL |
| `old_data/new_data` | NULL/NULL | Snapshots segundo a operação |
| `context_data` | `params`; pode acrescentar `response_id` antes da finalização | Cópia do contexto inicial da conexão, normalmente somente `params` |
| `request_id/correlation_id` | ID criado pela API, igual em todos os eventos da requisição | Herdados da conexão; fallback próprio para SQL sem contexto |
| `created_at`, hashes | Definidos pelo trigger da `hash chain` | Definidos pelo mesmo trigger da `hash chain` |

Não use o `entity_id` de um evento HTTP de recuperação de senha para descobrir o usuário-alvo: ele pode ser NULL. O alvo está no `password_changed.entity_id`, quando a senha é efetivamente modificada.

### Resultado e persistência

| `result` | Uso atual | Interpretação |
| --- | --- | --- |
| `pending` | `request_started` | A requisição entrou na execução auditada; ainda não havia resultado |
| `success` | Eventos de negócio, `transaction_committed`, resultados HTTP sem erro e seed | O significado exato depende do produtor |
| `failure` | Resultado HTTP com status >= 400 | O pedido terminou com erro; isso não afirma que todas as escritas foram revertidas |

O início **não é atualizado** para success/failure. Um novo evento final encerra a sequência logicamente. Isso preserva o modelo append-only.

Para um leitor externo sem leitura suja, um evento de negócio visível corresponde à escrita confirmada na mesma transação. Dentro da própria transação, ele ainda é provisório e pode desaparecer com rollback.

## Eventos do ciclo da requisição

### `request_started`

- **Gatilho:** `Audit::begin()`, depois de a rota ter sido encontrada e antes dos middlewares/controlador.
- **Filtro:** não é gerado para GET/HEAD.
- **Resultado:** `pending`; `http_status = NULL`.
- **Ator:** identidade da sessão no início; pode ser NULL.
- **Conteúdo:** método, modelo da rota, IP, user agent, IDs de correlação e parâmetros numéricos permitidos.
- **Garantia:** se sua inserção falhar, o roteador responde 503 e não executa middlewares/controlador.
- **Não significa:** usuário autorizado, validação aprovada ou alteração realizada.

O evento costuma persistir antes da transação do serviço. Assim, um rollback posterior das escritas não remove o início da requisição.

### `request_completed`

- **Gatilho:** `Audit::finish()` em rota diferente de login/logout, status < 400 e método diferente de GET/HEAD.
- **Resultado:** `success`; armazena o status HTTP final.
- **Conteúdo adicional:** `context_data.response_id` apenas quando `Controller::json()` recebeu um array com `id` numérico **no primeiro nível** e status < 400.
- **Não significa:** existência de INSERT/UPDATE/DELETE; uma operação sem mudança também pode terminar com sucesso.
- **Classificação:** status 3xx também satisfaz `status < 400` no código atual.

A finalização é executada pelo callback de shutdown. A proteção `finished` evita duplicação se `finish()` for chamado mais de uma vez.

### `operation_failed`

- **Gatilho:** finalização com status >= 400, exceto as rotas exatas `/auth/login` e `/auth/logout`, que têm eventos próprios.
- **Filtro:** também é gerado para GET/HEAD com erro.
- **Resultado:** `failure`, com `http_status`.
- **Abrange:** validações, bloqueios por middleware, falhas de autorização/CSRF, erros de servidor e outros erros HTTP.
- **Não armazena:** mensagem da exceção, corpo da requisição, credenciais ou proposta de alteração que não chegou ao banco.
- **Não significa:** rollback global da requisição.

Um GET que falha pode ter somente `operation_failed`, sem `request_started`. Isso é esperado.

### `transaction_committed`

- **Gatilho:** `Service::transaction()` chama `Audit::transaction()` imediatamente **antes** de `PDO::commit()` da transação externa.
- **Resultado:** `success`; `http_status = NULL`.
- **Atomicidade:** o próprio evento participa da transação; se o commit falhar ou houver rollback, ele não deve persistir.
- **Aninhamento:** transações internas usam savepoints e não geram um evento adicional ao liberar o savepoint.
- **Escopo:** exige contexto ativo de `Audit`. Não é emitido por qualquer chamada arbitrária a `PDO::commit()`, SQL em autocommit ou script sem contexto.
- **Não significa:** houve alteração em alguma tabela coberta; uma transação vazia ou com tabelas excluídas pode produzir esse evento.

Uma requisição pode confirmar duas transações externas e conter dois `transaction_committed`. Não há `transaction_id` separado nem evento `transaction_rolled_back` na implementação atual.

### Condições sem evento final na tabela

| Condição | Comportamento |
| --- | --- |
| OPTIONS tratado no bootstrap | Não entra no roteador auditado |
| Método/URL sem rota correspondente | Resposta 404 fora de `Audit::begin()`; sem evento nessa `hash chain` |
| Falha anterior ao início da auditoria | Pode não haver contexto nem registros |
| Finalização com transação ainda aberta | Não insere resultado; envia fallback ao log PHP |
| Falha ao inserir o resultado | Envia fallback; não há retry persistente implementado |
| Encerramento forçado sem shutdown | Pode deixar início sem resultado |

Portanto, “início sem final” é um sinal para investigação, não uma prova isolada de invasão ou de escrita perdida.

## Autenticação e senhas

| Evento | Condição exata | Resultado | Identidade registrada |
| --- | --- | --- | --- |
| `login` | Finalização de `/auth/login` com status < 400 | success | Releitura do ID da sessão e do nome após autenticar |
| `login_failed` | Mesma rota com status >= 400 | failure | Ator inicial; normalmente NULL, sem identificar a conta digitada |
| `logout` | Finalização de `/auth/logout` com status < 400 | success | Ator inicial preservado mesmo após destruir a sessão |
| `logout_failed` | Mesma rota com status >= 400 | failure | Ator inicial; pode ser NULL quando não há sessão válida |
| `password_changed` | UPDATE em `usuario` com comparação binária de OLD.senha e NEW.senha indicando diferença | success | Ator da conexão; alvo em `entity_type = usuario`, `entity_id = NEW.id` |

`login_failed` não distingue pelo nome do evento senha incorreta, conta inativa, restrição de perfil, rate limit ou erro de infraestrutura. Examine `http_status` e o fluxo da aplicação. O controlador atual, por exemplo, usa 429 para rate limit e 500 para PDOException.

`password_changed` tem `module = autenticacao`, `operation = update`, `http_status = NULL` e **ambos os snapshots nulos**. Nenhuma representação da senha é incluída. Uma substituição do hash por outro hash também gera o evento, mesmo que o texto original da senha fosse igual.

O bloco de senha é independente do bloco de campos permitidos: um UPDATE que altera nome e senha pode gerar `user_updated` ou `student_updated` **e** `password_changed`. Na inserção inicial de usuário não há `password_changed`.

Recuperação de senha e ativação de aluno continuam usando os eventos genéricos de requisição. A simples solicitação do email de recuperação não gera `password_changed`. Na redefinição via token, o ator pode ser NULL, mas o ID do usuário-alvo aparece no evento do trigger. Uma ativação que muda ativo e senha pode gerar `student_status_changed` e `password_changed`.

## Catálogo de eventos por tabela

A matriz mostra os nomes-base para SQL direto. A coluna UPDATE deve ser lida junto das substituições e prioridades em [Regras de classificação](#regras-de-classificação). DELETE significa exclusão física; desativação normalmente é UPDATE.

| `entity_type` | `module` | `entity_id` | INSERT | UPDATE-base | DELETE |
| --- | --- | --- | --- | --- | --- |
| `usuario` | `usuarios` | `id` | `user_created` | `user_updated` | `user_deleted` |
| `aluno` | `alunos` | `usuario_id` | `student_created` | `student_updated` | `student_deleted` |
| `funcionario` | `usuarios` | `usuario_id` | `employee_created` | `employee_updated` | `employee_deleted` |
| `endereco` | `usuarios` | `id` | `address_created` | `address_updated` | `address_deleted` |
| `contato` | `usuarios` | `id` | `contact_created` | `contact_updated` | `contact_deleted` |
| `cargo` | `usuarios` | `id` | `role_created` | `role_updated` | `role_deleted` |
| `permissao` | `usuarios` | `id` | `permission_created` | `permission_updated` | `permission_deleted` |
| `cargo_permissao` | `usuarios` | `cargo_id` | `permission_changed` | `permission_changed` | `permission_changed` |
| `aluno_turma` | `matriculas` | `id` | `enrollment_created` | `enrollment_updated` | `enrollment_deleted` |
| `servico` | `financeiro` | `id` | `service_created` | `service_updated` | `service_deleted` |
| `plano` | `configuracao` | `id` | `plan_created` | `plan_updated` | `plan_deleted` |
| `plano_item` | `configuracao` | `id` | `plan_item_created` | `plan_item_updated` | `plan_item_deleted` |
| `contrato` | `financeiro` | `id` | `contract_created` | `contract_updated` | `contract_deleted` |
| `contrato_item` | `financeiro` | `id` | `contract_item_created` | `contract_item_updated` | `contract_item_deleted` |
| `cobranca` | `financeiro` | `id` | `invoice_created` | `invoice_updated` | `invoice_deleted` |
| `pagamento` | `financeiro` | `id` | `payment_created` | `payment_updated` | `payment_deleted` |
| `modalidade` | `configuracao` | `id` | `modality_created` | `modality_updated` | `modality_deleted` |
| `treino` | `treinos` | `id` | `workout_created` | `workout_updated` | `workout_deleted` |
| `espaco_treino` | `configuracao` | `id` | `location_created` | `location_updated` | `location_deleted` |
| `turma` | `turmas` | `id` | `class_created` | `class_updated` | `class_deleted` |
| `turma_config_horario` | `turmas` | `id` | `schedule_created` | `schedule_updated` | `schedule_deleted` |
| `treino_agenda` | `treinos` | `id` | `workout_session_created` | `workout_session_updated` | `workout_session_deleted` |
| `presenca_treino` | `presencas` | `treino_id` | `attendance_created` | `attendance_updated` | `attendance_deleted` |

### Distinções necessárias

- `usuario` guarda dados comuns de admin, funcionário e aluno. INSERT/DELETE nessa tabela usam `user_created/user_deleted`, inclusive para usuários do tipo aluno.
- O registro em `aluno` usa `student_created`, `student_updated` e `student_deleted`. UPDATE de dados comuns em `usuario` também pode usar `student_updated`. Diferencie pela coluna `entity_type`; os módulos são, respectivamente, `alunos` e `usuarios`.
- `aluno_turma` representa vínculo com uma turma: `enrollment_*`. Não é o mesmo que criar o cadastro em `aluno` nem assinar um contrato financeiro.
- `cargo` usa `role_created`, `role_updated` e `role_deleted`; mudar a atribuição de cargo de um funcionário usa `role_changed` em `funcionario`.
- `permissao` usa `permission_created`, `permission_updated` e `permission_deleted`; atribuir/remover vínculo em `cargo_permissao` usa `permission_changed`.
- Em `presenca_treino`, `entity_id = treino_id` referencia `treino_agenda.id`; o par completo é `(treino_id, aluno_id)`.
- Em `cargo_permissao`, o par completo é `(cargo_id, permissao_id)`. Vários eventos com o mesmo entity_id podem tratar de permissões diferentes.

### Eventos não implementados

Não existem emissores específicos de `payment_cancelled`, `refund_created`, `setting_changed`, `transaction_rolled_back` ou `audit_tamper_detected`. `payment_deleted` não deve ser interpretado automaticamente como estorno. Eventos específicos do catálogo podem existir nos triggers mesmo sem um endpoint correspondente disponível hoje.

## Regras de classificação

### Regra comum

O trigger compara cada campo permitido com:

```sql
NOT (BINARY OLD.campo <=> BINARY NEW.campo)
```

O operador compara NULL de forma explícita; o BINARY evita ignorar diferenças de caixa/acentuação na detecção da mudança. Se **nenhum campo permitido mudou**, não há evento-base de UPDATE. A senha é verificada separadamente.

Os snapshots incluem todos os campos permitidos da tabela, não apenas os campos diferentes. Mudanças em campos fora da lista não produzem evento por si só, exceto a regra especial de senha. Campos automáticos de atualização não estão na lista e não geram ruído sozinhos.

### Campo `ativo`

Quando `OLD.ativo <=> NEW.ativo` é falso, o evento-base é substituído:

| Tabela | Evento de mudança de ativo |
| --- | --- |
| `usuario`, NEW.tipo_usuario diferente de aluno | `user_status_changed` |
| `usuario`, NEW.tipo_usuario = aluno | `student_status_changed` |
| `cargo` | `role_status_changed` |
| `aluno_turma` | `enrollment_status_changed`, com exceção de cancelamento abaixo |
| `servico` | `service_status_changed` |
| `plano` | `plan_status_changed` |
| `modalidade` | `modality_status_changed` |
| `treino` | `workout_status_changed` |
| `espaco_treino` | `location_status_changed` |
| `turma` | `class_status_changed` |

O nome não diferencia ativação de desativação; use `old_data.ativo/new_data.ativo`.

Em `aluno_turma`, a transição **1 → 0** tem prioridade e gera `enrollment_cancelled`. A reativação **0 → 1** gera `enrollment_status_changed`, não `enrollment_created`. Exclusão física gera `enrollment_deleted`.

### Campo `status`

| Tabela | Condição de transição | Evento |
| --- | --- | --- |
| `contrato` | status mudou e NEW.status = cancelado | `contract_cancelled` |
| `contrato` | status mudou para outro valor, como pausado/encerrado/ativo | `contract_status_changed` |
| `cobranca` | status mudou e NEW.status = cancelada | `invoice_cancelled` |
| `cobranca` | status mudou e NEW.status = paga | `invoice_paid` |
| `cobranca` | status mudou para outro valor, como aberta/vencida | `invoice_status_changed` |
| `treino_agenda` | status mudou e NEW.status = cancelado | `workout_session_cancelled` |
| `treino_agenda` | status mudou para outro valor, como agendado/concluido | `workout_session_status_changed` |

Se o status não muda, mas outro campo permitido muda, o evento permanece `*_updated`. Inserir uma cobrança já com status paga gera `invoice_created`, não `invoice_paid`: a regra especial pertence ao UPDATE.

`invoice_paid` demonstra mudança do estado da cobrança; não comprova, sozinho, que há um pagamento bancário correspondente. SQL administrativo também pode mudar esse estado.

### Usuário e atribuição de cargo

Para UPDATE de `usuario`, a prioridade é:

1. Se `tipo_usuario` mudou: `role_changed`.
2. Caso contrário, se `ativo` mudou: `user_status_changed`.
3. Caso contrário: `user_updated`.
4. Se NEW.tipo_usuario = aluno, substitui `user_` por `student_` no nome escolhido; `role_changed` permanece igual.

Consequências:

- Alterar simultaneamente tipo e ativo produz um único evento-base `role_changed`; ambas as mudanças aparecem nos snapshots.
- Trocar aluno para funcionário ou funcionário para aluno produz `role_changed`, não um evento de cadastro novo.
- Alterar os campos próprios da tabela `aluno` usa `student_updated` independentemente dessa regra de `usuario`.

Para UPDATE de `funcionario`, alterar `cargo_id` tem prioridade e gera `role_changed`; sem essa mudança, usa `employee_updated`.

Para `cargo_permissao`, todas as operações usam `permission_changed`. Interprete INSERT como concessão, DELETE como revogação e UPDATE como mudança do vínculo. A exclusão em cascata de um vínculo mantém esse nome.

### Mudanças simultâneas

Não há um evento para cada campo alterado. Em geral, há um evento por linha/operação, com o nome mais prioritário e snapshots completos. A exceção implementada é o evento adicional de senha.

Exemplo: mudar `plano.valor` e `plano.ativo` no mesmo UPDATE gera `plan_status_changed`, não dois eventos. Para calcular tudo o que mudou, compare os snapshots; não derive a lista de mudanças apenas de `action`.

## Contrato dos snapshots

| Operação | `old_data` | `new_data` |
| --- | --- | --- |
| INSERT | SQL NULL | Objeto com campos permitidos após inserir |
| UPDATE | Objeto anterior | Objeto posterior |
| DELETE | Objeto anterior | SQL NULL |
| PHP/seed_applied/password_changed | SQL NULL | SQL NULL |

SQL NULL em uma coluna JSON é diferente de uma propriedade JSON com valor null. Na exclusão de um plano, por exemplo, `new_data IS NULL`; em SET NULL de uma FK de contrato, `new_data` é um objeto cujo `plano_id` é null.

### Lista completa de campos registrados

A lista abaixo corresponde aos campos explicitamente selecionados nos triggers de `docs/sql/auditoria_operacoes.sql`. Não há captura automática de todas as colunas.

| Tabela | Chaves de old_data/new_data |
| --- | --- |
| `usuario` | `id`, `nome`, `sobrenome`, `email`, `tipo_usuario`, `ativo`, `endereco_id` |
| `aluno` | `usuario_id`, `data_matricula`, `cadastrado_por`, `codigo_matricula` |
| `funcionario` | `usuario_id`, `cargo_id`, `registro_profissional` |
| `endereco` | `id`, `logradouro`, `numero`, `cidade`, `bairro`, `cep`, `complemento` |
| `contato` | `id`, `usuario_id`, `tipo`, `valor` |
| `cargo` | `id`, `nome`, `descricao`, `ativo`, `salario_base` |
| `permissao` | `id`, `slug`, `descricao` |
| `cargo_permissao` | `cargo_id`, `permissao_id` |
| `aluno_turma` | `id`, `aluno_id`, `turma_id`, `data_inscricao`, `ativo` |
| `servico` | `id`, `nome`, `tipo`, `valor_base`, `recorrente`, `ativo` |
| `plano` | `id`, `nome`, `periodicidade`, `valor`, `ativo` |
| `plano_item` | `id`, `plano_id`, `servico_id`, `quantidade`, `valor_unitario` |
| `contrato` | `id`, `aluno_id`, `plano_id`, `data_inicio`, `data_fim`, `dia_vencimento`, `valor_contratado`, `desconto`, `multa_percentual`, `juros_percentual`, `status` |
| `contrato_item` | `id`, `contrato_id`, `servico_id`, `descricao`, `quantidade`, `valor_unitario`, `valor_desconto` |
| `cobranca` | `id`, `contrato_id`, `aluno_id`, `competencia`, `descricao`, `valor_original`, `desconto`, `multa`, `juros`, `valor_final`, `data_vencimento`, `status` |
| `pagamento` | `id`, `cobranca_id`, `valor_pago`, `data_pagamento`, `forma_pagamento`, `registrado_por` |
| `modalidade` | `id`, `nome`, `descricao`, `ativo` |
| `treino` | `id`, `nome`, `modalidade_id`, `descricao`, `ativo` |
| `espaco_treino` | `id`, `nome`, `capacidade_minima`, `capacidade_maxima`, `ativo` |
| `turma` | `id`, `nome`, `instrutor_id`, `capacidade_minima`, `capacidade_maxima`, `ativo` |
| `turma_config_horario` | `id`, `turma_id`, `dia_semana`, `hora_inicio`, `hora_fim` |
| `treino_agenda` | `id`, `treino_id`, `turma_id`, `espaco_id`, `instrutor_id`, `data_hora_inicio`, `data_hora_fim`, `status` |
| `presenca_treino` | `treino_id`, `aluno_id`, `situacao`, `checkin_time` |

Identificadores de transação do pagamento, textos livres excluídos e dados pessoais fora da lista não aparecem. Os JSONs não são necessariamente adequados para restaurar uma tabela inteira: faltam campos deliberadamente não auditados.

### Exemplo de evento de status

Projeção ilustrativa de um evento — os hashes foram omitidos, pois seriam calculados sobre a linha completa:

```json
{
  "action": "student_status_changed",
  "operation": "update",
  "module": "usuarios",
  "entity_type": "usuario",
  "entity_id": 1523,
  "user_id": 7,
  "user_name": "João Silva",
  "request_id": "0123456789abcdef0123456789abcdef",
  "correlation_id": "0123456789abcdef0123456789abcdef",
  "http_method": "PUT",
  "route": "/alunos/{id}/desativar",
  "context_data": {"params": {"id": 1523}},
  "old_data": {
    "id": 1523,
    "nome": "Ana",
    "sobrenome": "Souza",
    "email": "ana@example.test",
    "tipo_usuario": "aluno",
    "ativo": 1,
    "endereco_id": 20
  },
  "new_data": {
    "id": 1523,
    "nome": "Ana",
    "sobrenome": "Souza",
    "email": "ana@example.test",
    "tipo_usuario": "aluno",
    "ativo": 0,
    "endereco_id": 20
  },
  "result": "success",
  "http_status": null,
  "ip_address": "192.168.1.20"
}
```

O evento final da mesma rota tem `module = alunos`, enquanto o evento do trigger acima tem `module = usuarios`. Isso decorre dos dois produtores; correlacione pelo request_id.

## Cascatas, lotes e regravações

### Cascatas de exclusão

Triggers `audit_<pai>_cascade`, executados BEFORE DELETE, capturam os filhos cobertos. Os eventos dos filhos podem ter IDs menores que o evento DELETE do próprio pai.

| Efeito da FK | Operação auditada no filho | Snapshots | Nome |
| --- | --- | --- | --- |
| ON DELETE CASCADE | DELETE | Linha anterior / NULL | `<prefix>_deleted` |
| ON DELETE SET NULL | UPDATE | Linha anterior / mesma linha com a FK nula | `<prefix>_updated` |

A exceção de nome é `cargo_permissao`, que continua com `permission_changed`. As regras especiais de classificação do UPDATE direto **não são executadas** pelos triggers de eventos de cascata.

Exemplos: excluir um plano pode gerar `plan_item_deleted`, `contract_updated` com plano_id nulo e `plan_deleted`. Excluir usuário pode gerar eventos de filhos em múltiplos níveis, como `student_deleted` e `enrollment_deleted`.

Não existe um campo `cascade = true` nem `parent_event_id`. A análise usa entidade, FK nos snapshots, ordem e correlação. Esses elementos ajudam a interpretar a sequência, mas o formato não identifica explicitamente a causa SQL da cascata.

Se a exclusão do pai falhar por uma restrição ou sofrer rollback, seus eventos de cascata também são revertidos.

### Lotes e sincronia de presenças

Um UPDATE que afeta dez linhas com diferenças permitidas gera dez eventos. A ordem entre linhas de um lote não deve ser interpretada como ordem de uma escolha do usuário.

`TurmaRepository::syncPresencasTreino()` exclui e reinsere presenças do conjunto editável. Por isso, uma confirmação com o mesmo estado final pode conter `attendance_deleted` seguido de `attendance_created`. Isso registra o SQL executado; não implica que o aluno faltou e depois compareceu. Presenças fora do conjunto editável são preservadas.

## Exemplos de sequências correlacionadas

Todos os itens de cada exemplo compartilham request_id/correlation_id. IDs globais podem ser intercalados com outras requisições antes ou depois da transação.

### Criar plano com dois itens

```text
request_started             pending
plan_created                success
plan_item_created           success
plan_item_created           success
transaction_committed       success
request_completed           success, http_status definido pelo controlador
```

O plano, seus itens e o marcador de commit pertencem à mesma transação de `FinanceiroService::criarPlano()`.

### Falha dentro da transação de criação

```text
request_started             pending
operation_failed            failure
```

Se a validação de um item falhar após inserir o plano, os eventos de plano/itens e o `chain head` provisório sofrem rollback. Não sobra um `plan_created` persistido dessa tentativa.

### Registrar pagamento integral

No `FinanceiroService::registrarPagamento()` atual, as escritas não são envolvidas em `Service::transaction()`:

```text
request_started             pending
payment_created             success
invoice_paid                success
request_completed           success
```

Não espere `transaction_committed` nesse fluxo. Cada escrita e seu evento são atômicos, mas o conjunto pagamento + alteração da cobrança não tem atomicidade global por esse método. Se a segunda escrita falhar após confirmar a primeira, pode haver `payment_created` seguido de `operation_failed`, sem `invoice_paid`. Esta documentação descreve essa limitação do fluxo atual; não atribui ao hash uma garantia transacional que o serviço não oferece.

Pagamento parcial normalmente gera `payment_created` sem `invoice_paid`.

### Login recusado

```text
request_started             pending, user_id geralmente NULL
login_failed                failure, http_status 401/429/500 conforme a causa
```

Não há conta digitada, senha nem token no log. A presença de login_failed não confirma que o usuário informado existe.

### Cadastro confirmado, falha posterior no email

`AlunoService::create()` confirma o cadastro e depois chama o envio do convite. É possível encontrar eventos de cadastro, um ou mais commits e, em seguida, `operation_failed`. Não conclua que o aluno não foi criado somente pelo erro HTTP; consulte os eventos de negócio.

## Evento de carga e diagnóstico fora da `hash chain`

### `seed_applied`

O script [sistema_seed.sql](../sql/testes/sistema_seed.sql) insere esse marcador se ainda não existe um evento com esse nome.

- `operation = insert`, `module = sistema`, `result = success`.
- Define `user_id = @admin_id`; não preenche `user_name`, entidade ou snapshots explicitamente.
- O trigger de integridade calcula seus hashes e completa IDs conforme o contexto disponível.
- É um marcador da carga de desenvolvimento, não uma prova de execução de cada instrução nem um evento de deploy.

### Fallbacks do log PHP

Esses nomes aparecem em linhas `[AUDITORIA]` do log de erro PHP, não em `audit_logs.action`:

| `event` | Condição | Consequência |
| --- | --- | --- |
| `auditoria_indisponivel` | Exceção no início da auditoria | Roteador interrompe a execução com 503 |
| `requisicao_finalizada_com_transacao_aberta` | Shutdown encontra PDO em transação | Não registra resultado que poderia sugerir sucesso sem commit |
| `falha_ao_registrar_resultado` | Exceção durante finalização | Resultado final pode faltar na `hash chain` |

O payload é limitado a request_id e event. Esses fallbacks não têm a `hash chain` SHA-256 da tabela.

A rejeição de UPDATE/DELETE em audit_logs usa SIGNAL e desfaz a instrução; não produz `audit_tamper_detected`. O verificador reporta divergências no CLI e não insere eventos no histórico que está verificando.

## Consultas de investigação

Os `?` abaixo são parâmetros para PDO/prepared statements. Não concatene entrada do usuário ao SQL.

### Sequência completa de uma requisição

```sql
SELECT id, created_at, action, operation, module,
       user_id, user_name, entity_type, entity_id,
       result, http_status, context_data, old_data, new_data
FROM audit_logs
WHERE request_id = ?
ORDER BY id;
```

### Alterações de uma entidade

```sql
SELECT id, created_at, user_id, action, old_data, new_data, request_id
FROM audit_logs
WHERE entity_type = ? AND entity_id = ?
ORDER BY id;
```

Para presença, o treino sozinho não identifica o aluno:

```sql
SELECT id, action, old_data, new_data, request_id
FROM audit_logs
WHERE entity_type = 'presenca_treino'
  AND entity_id = ?
  AND COALESCE(
      JSON_UNQUOTE(JSON_EXTRACT(new_data, '$.aluno_id')),
      JSON_UNQUOTE(JSON_EXTRACT(old_data, '$.aluno_id'))
  ) = ?
ORDER BY id;
```

### Inícios sem evento final

```sql
SELECT s.id, s.created_at, s.request_id, s.route, s.user_id
FROM audit_logs s
WHERE s.action = 'request_started'
  AND s.created_at < UTC_TIMESTAMP() - INTERVAL 5 MINUTE
  AND NOT EXISTS (
      SELECT 1 FROM audit_logs f
      WHERE f.request_id = s.request_id
        AND f.action IN (
            'request_completed', 'operation_failed',
            'login', 'login_failed', 'logout', 'logout_failed'
        )
  )
ORDER BY s.id;
```

Cinco minutos é somente uma janela ilustrativa: ajuste à duração legítima dos fluxos. Ausência de final também pode significar execução ainda em andamento.

### Conferir transição de cobrança para paga

```sql
SELECT id, entity_id AS cobranca_id,
       JSON_UNQUOTE(JSON_EXTRACT(old_data, '$.status')) AS antes,
       JSON_UNQUOTE(JSON_EXTRACT(new_data, '$.status')) AS depois,
       user_id, request_id
FROM audit_logs
WHERE action = 'invoice_paid'
  AND entity_type = 'cobranca'
ORDER BY id DESC
LIMIT 100;
```

Essas consultas leem fatos; **não verificam criptograficamente** a `hash chain`. Para isso use o CLI descrito em [Operação da auditoria](auditoria_operacoes.md). A consulta geral está em [auditoria_leitura.sql](../sql/auditoria_leitura.sql).

## Relação com a documentação do módulo

- [Módulo Auditoria](auditoria.md): acesso, fluxo, dados e componentes.
- [Operação da auditoria](auditoria_operacoes.md): instalação, integridade, verificação e diagnóstico.
