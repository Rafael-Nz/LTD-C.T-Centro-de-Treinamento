# Modulo Financeiro

## Objetivo

Controla os servicos vendidos pelo centro de treinamento, os planos comerciais, os contratos dos alunos, as cobrancas e os pagamentos recebidos.

Os valores sao copiados para o contrato e para a cobranca. Dessa forma, uma alteracao futura de preco nao altera o historico financeiro.

## Visao geral e contratos

A visao geral (`/admin/financeiro`) reune os indicadores financeiros e a tabela de contratos. Segue o componente compartilhado `tabelas.js`, com 10 registros por pagina, busca por aluno/plano e filtros combinados de plano e status. A busca, os filtros e a paginacao sao executados no banco. O endpoint de contratos aceita o protocolo DataTables quando recebe `draw`; sem esse parametro, preserva a resposta de lista para outros consumidores.

A tela separada de contratos foi removida do menu. O endereco antigo redireciona para a visao geral. O cadastro de contrato continua disponivel pelo botao Novo contrato, retornando para a visao geral ao salvar ou voltar.

## Atualizacao de seguranca e arquitetura Just-In-Time (JIT)

Em banco existente, aplicar **uma vez**, usando uma conta administradora:
1. `docs/sql/migrations/financeiro_seguranca.sql` (estrutura inicial de integridade financeira);
2. `docs/sql/migrations/cobrancas_dinamicas_jit.sql` (triggers automaticas de vencimento e suporte dinamico).

Nao executar `setup/banco.sql` no banco existente: esse arquivo recria o banco. Fazer backup antes da migracao. A migracao preserva recebimentos e cobrancas; cria campos, indices e atualiza os gatilhos de auditoria. DDL no MariaDB tem commit implicito: pare e investigue qualquer erro, sem continuar a execucao de um arquivo parcialmente aplicado.

O preflight rejeita referencias bancarias duplicadas e uma migracao ja aplicada/parcial. Revisar duplicidades manualmente; nao apagar historico para forcar o indice. A conta `ctt_app` continua restrita: nao conceder ALTER/CREATE/TRIGGER a ela. A API informa atualizacao pendente enquanto os campos novos nao existem.

### Automacao Just-In-Time (Substituicao do Agendador do Windows)
O sistema foi refatorado para utilizar o modelo **Just-In-Time (JIT)**, eliminando a dependencia de tarefas externas no Agendador de Tarefas do Windows:
- **Disparo Automatico:** Qualquer requisicao aos endpoints `/api/financeiro/cobrancas`, `/api/financeiro/contratos` ou `/api/financeiro/metricas` aciona `FinanceiroService::processarJustInTime()`.
- **Sincronizacao em Tempo Real:** Faturas com data expirada sao sincronizadas para status `vencida` em milissegundos (< 1ms).
- **Triggers de Integridade:** Triggers no banco de dados (`BEFORE INSERT` e `BEFORE UPDATE`) convertem cobrancas retroativas para `vencida` instantaneamente.
- **Throttle Diario Inteligente:** Para evitar varreduras desnecessarias de contratos, o carimbo da ultima geracao do dia e mantido em `tools/financeiro/financeiro_jit_state.json`.
- **Geracao Imediata em Contratos:** Ao salvar ou reativar um contrato (`criarContrato()` ou `statusContrato()`), as cobrancas devidas sao criadas imediatamente sem necessidade de aguardar qualquer rotina.
- **Execucao CLI Opcional:** O script `php tools/financeiro/gerar_cobrancas.php` permanece disponivel caso deseje executar faturamentos manuais ou via crontab em servidores Linux.

## Recebimentos e correcoes

- Recebimento, recalculo do saldo e status da cobranca usam uma unica transacao. O bloqueio `FOR UPDATE` da cobranca dura ate o commit. Pagamentos, cancelamentos e estornos seguem a mesma ordem de bloqueio (cadeia de auditoria, cobranca, pagamento).
- Toda nova solicitacao de pagamento exige `chave_idempotencia` (16 a 64 caracteres alfanumericos, hifen ou sublinhado). Reenviar a mesma chave e os mesmos dados devolve o ID original; reutilizar com dados diferentes e rejeitado. O indice unico protege inclusive conexoes concorrentes.
- A tela mantem a operacao pendente na sessao do navegador em caso de falha de comunicacao e reutiliza a chave. Erros de validacao liberam uma nova tentativa corrigida. Uma nova operacao realmente distinta usa outra chave. Referencia bancaria opcional, quando preenchida, tambem e unica; o sistema nao pode identificar dois recebimentos em dinheiro como sendo o mesmo evento real se forem enviados com chaves novas.
- Valores sao validados antes da conversao do DTO e calculados em centavos inteiros. Nao ha tolerancia para receber um centavo acima do saldo. Datas precisam existir; pagamento futuro e recusado. Contratos validam vigencia, vencimento de 1 a 28, periodicidade, descontos e percentuais de zero a 100.
- Cancelar exige justificativa de 5 a 500 caracteres e ausencia de recebimentos nao estornados. A cobranca permanece no historico e nao e recriada pela geracao automatica.
- Estorno e **integral por pagamento**, exige justificativa e preserva o registro original, data e responsavel. O saldo e recalculado e a cobranca volta a aberta/vencida, conforme a data. Repetir estorno/cancelamento nao duplica efeitos nem troca a justificativa original. Reenviar uma chave de pagamento ja estornado nao recria o recebimento.
- Estorno e um registro interno: eventual devolucao via Pix/cartao deve ser efetuada fora deste modulo. Nao ha estorno parcial nesta implementacao.
- Os gatilhos registram `payment_reversed` e os motivos de estorno/cancelamento na auditoria. A lista de cobrancas mostra total recebido e saldo liquidos de estornos.

## Geracao automatica e encargos moratorios

### Catalogo e contratos

Servicos e planos podem ser editados, ativados ou desativados. Essas alteracoes nao modificam valores ja registrados em contratos e cobrancas, preservando o historico financeiro.

Contratos podem assumir os estados `ativo`, `pausado`, `encerrado` ou `cancelado`. Contratos encerrados ou cancelados nao podem ser reabertos, e contratos com vigencia expirada nao podem ser retomados. Alterar o estado do contrato nao cancela cobrancas nem estorna pagamentos existentes.

Ao retomar um contrato pausado, a rotina pode gerar cobrancas referentes ao periodo da pausa, pois o sistema nao mantem intervalos de suspensao para conceder isencoes automaticas.

### Calendario de cobrancas

Todo contrato ativo participa da geracao automatica. A periodicidade (`mensal`, `trimestral`, `semestral`, `anual` ou `avulso`) e copiada para o contrato, garantindo que alteracoes posteriores no plano nao modifiquem seu calendario.

O primeiro vencimento e a primeira ocorrencia do dia escolhido igual ou posterior ao inicio da vigencia. Por exemplo, um contrato trimestral iniciado em 15/01, com vencimento no dia 10, gera cobrancas em 10/02, 10/05 e 10/08. Contratos avulsos geram uma unica cobranca e nao ha rateio proporcional automatico.

A rotina gera competencias pendentes ate o mes atual e respeita a data final do contrato. Contratos pausados, encerrados ou cancelados sao ignorados. A operacao e idempotente: executa-la novamente nao duplica cobrancas, e uma cobranca cancelada continua vinculada a sua competencia.

### Calculo de Encargos Moratorios (Multa e Juros)

O metodo `FinanceiroService::calcularEncargos()` calcula dinamicamente o valor atualizado das faturas vencidas:
- **Multa:** Percentual fixo contratual aplicado sobre o valor base líquido (`valor_original - desconto`).
- **Juros:** Juros contratuais mensais calculados no regime pró-rata dia (`(juros_percentual / 100) / 30 * dias_atraso`).
- Os encargos nao alteram a fatura original no banco antes da quitacao; sao exibidos ao operador e consolidados no momento do recebimento.

## Testes

Execute a suite rapida, sem acessar o MySQL, antes de cada commit:

```powershell
composer test
```

Esse comando cobre calendario de cobrancas, regras de edicao e status, paginacao, filtros, contagens, saldos e exclusao de pagamentos estornados. Os testes de repositorio usam SQLite em memoria e o teste de acoes usa um repositorio falso; nenhum deles depende do banco da aplicacao.

Para testar especificamente as triggers e o mecanismo Just-In-Time contra o MySQL:
```powershell
php tools/testes/financeiro/test_jit_triggers.php
```

Os cenarios completos de integracao exigem um MariaDB isolado em `127.0.0.1:33317`, com diretorio de dados cujo caminho contenha `ctt-financeiro-`. O teste recusa qualquer outro servidor para proteger o banco normal. Com o servidor isolado em execucao, use:

```powershell
composer test:financeiro:setup
composer test:financeiro:integracao
composer test:financeiro:migracao
```

O setup recria `ctt_financeiro_test` e aplica o seed. A integracao testa centavos, datas, calendario, pagamentos parciais, reenvios, estornos, cancelamentos, auditoria, rollback e duas conexoes concorrentes. O teste de migracao simula a estrutura anterior, aplica `financeiro_seguranca.sql` e verifica a compatibilidade dos contratos existentes. O marcador legado de auditoria do seed e omitido por incompatibilidade de `INSERT SELECT` nessa versao do MariaDB.

## Rotas

Todas as rotas exigem `AuthMiddleware`.

| Metodo | Rota | Funcao |
| --- | --- | --- |
| GET | `/financeiro/servicos` | Lista servicos. Use `ativos=false` para incluir inativos. |
| GET | `/financeiro/servicos/{id}` | Detalha um servico. |
| POST | `/financeiro/servicos` | Cadastra servico. |
| PUT | `/financeiro/servicos/{id}` | Atualiza servico. |
| PUT | `/financeiro/servicos/{id}/status` | Ativa ou desativa um servico. |
| GET | `/financeiro/planos` | Lista planos. |
| GET | `/financeiro/planos/{id}` | Detalha um plano e seus itens. |
| POST | `/financeiro/planos` | Cadastra plano e seus itens. |
| PUT | `/financeiro/planos/{id}` | Atualiza um plano. |
| PUT | `/financeiro/planos/{id}/status` | Ativa ou desativa um plano. |
| GET | `/financeiro/contratos` | Lista contratos. Aceita `aluno_id`. |
| GET | `/financeiro/contratos/{id}` | Detalha contrato e itens. |
| POST | `/financeiro/contratos` | Contrata um plano ou servicos para um aluno. |
| PUT | `/financeiro/contratos/{id}/status` | Altera o estado do contrato. |
| POST | `/financeiro/contratos/{id}/cobrancas` | Gera cobranca de uma competencia. |
| GET | `/financeiro/cobrancas` | Lista cobrancas. Aceita `aluno_id` e `status`. |
| GET | `/financeiro/cobrancas/{id}` | Detalha cobranca, pagamentos e saldo. |
| POST | `/financeiro/cobrancas/{id}/pagamentos` | Registra pagamento total ou parcial. |
| POST | `/financeiro/cobrancas/{id}/cancelar` | Cancela uma cobranca mediante justificativa. |
| POST | `/financeiro/cobrancas/{id}/pagamentos/{pagamento_id}/estornar` | Estorna integralmente um pagamento mediante justificativa. |
| POST | `/financeiro/cobrancas/gerar-automaticas` | Gera competencias pendentes dos contratos ativos. |
| POST | `/financeiro/cobrancas/atualizar-vencidas` | Marca cobrancas abertas vencidas. |
| GET | `/financeiro/metricas` | Consolida faturamento, recebido, a receber e inadimplencia. |
| GET | `/financeiro/cobrancas/{id}/recibo/{pagamento_id}` | Emite comprovante/recibo com dados do estabelecimento. |

## Exemplos de payload

Servico:

```json
{
  "nome": "Mensalidade funcional",
  "tipo": "mensalidade",
  "valor_base": 150,
  "recorrente": true
}
```

Plano:

```json
{
  "nome": "Plano funcional mensal",
  "periodicidade": "mensal",
  "valor": 150,
  "itens": [
    { "servico_id": 1, "quantidade": 1, "valor_unitario": 150 }
  ]
}
```

Contrato:

```json
{
  "aluno_id": 10,
  "plano_id": 1,
  "data_inicio": "2026-09-10",
  "dia_vencimento": 10,
  "desconto": 0
}
```

Geracao de cobranca:

```json
{
  "competencia": "2026-09",
  "data_vencimento": "2026-09-10"
}
```

Pagamento:

```json
{
  "valor_pago": 150,
  "chave_idempotencia": "a91be47e-64cf-4765-a354-5bcf6771cc61",
  "forma_pagamento": "pix",
  "transacao_id": "pix-20260910-001"
}
```

## Regras

As listagens de Servicos (`/admin/financeiro/servicos`) e Planos (`/admin/financeiro/planos`) tem acessos separados no menu Financeiro, com busca, filtros e paginacao. Os botoes de cadastro abrem formularios proprios em `/admin/financeiro/servicos/cadastrar` e `/admin/financeiro/planos/cadastrar`; apos salvar, retornam a respectiva listagem. Nao ha uma aba adicional em Configuracoes.

- O dia de vencimento aceita valores de 1 a 28 para evitar datas inexistentes.
- A mesma competencia nao pode ser gerada duas vezes para o mesmo contrato.
- Pagamentos parciais sao permitidos, mas nao podem ultrapassar o saldo.
- A cobranca muda para `paga` quando o saldo chega a zero.
- O status de cobrancas vencidas e faturamentos pendentes e sincronizado automaticamente via triggers SQL e pelo mecanismo Just-In-Time (JIT) a cada requisicao, dispensando o agendador de tarefas do sistema operacional.
- Encargos moratorios (multa contratual fixa e juros proporcionais ao dia de atraso) sao calculados dinamicamente via `FinanceiroService::calcularEncargos()`.
