# Módulo Relatório

## Objetivo

Disponibiliza métricas operacionais, relatórios tabulares e exportações dos principais dados do centro de treinamento. O módulo é somente leitura e consolida informações de alunos, presença, avaliações físicas, turmas, funcionários e agenda de treinos.

## Diretório

- [`api/src/relatorio`](../../api/src/relatorio)

## Acesso

Todas as rotas do módulo usam `AuthMiddleware`. É necessário enviar uma sessão ou credencial válida conforme a configuração de autenticação da API.

## Rotas principais

### `GET /relatorios/metricas`

Retorna indicadores gerais do sistema:

| Campo | Descrição |
| --- | --- |
| `total_alunos` | Total de alunos cadastrados. |
| `alunos_ativos` | Alunos vinculados a usuários ativos. |
| `turmas_ativas` | Total de turmas ativas. |
| `turmas_total` | Total de turmas cadastradas. |
| `avaliacoes_mes` | Avaliações físicas registradas no mês atual. |
| `funcionarios_ativos` | Funcionários vinculados a usuários ativos. |
| `presencas` | Presenças registradas nos últimos 30 dias. |
| `presencas_total` | Total de registros de presença nos últimos 30 dias. |
| `taxa_presenca` | Percentual de presenças sobre o total no período, com duas casas decimais. |

Quando não houver registros de presença no período, `taxa_presenca` será `0`.

### `GET /relatorios/gerar`

Gera um relatório em JSON. O parâmetro obrigatório `tipo` aceita:

- `alunos`
- `presenca`
- `avaliacoes`
- `turmas`
- `funcionarios`
- `treinos`

Resposta conceitual:

```json
{
  "tipo": "alunos",
  "registros": []
}
```

O núcleo HTTP pode incluir esse resultado dentro de uma propriedade `data`, conforme o formato padrão de resposta da API.

### `GET /relatorios/exportar`

Gera o relatório escolhido e retorna um arquivo para download. Além do parâmetro `tipo` e dos filtros descritos abaixo, aceita:

| Parâmetro | Valores | Padrão |
| --- | --- | --- |
| `formato` | `csv` ou `xlsx` | `csv` |

Os arquivos são nomeados como `relatorio-{tipo}.csv` ou `relatorio-{tipo}.xlsx`. No CSV, as colunas usam `;` como separador e o arquivo é emitido com BOM UTF-8. A exportação XLSX depende da extensão PHP `ZipArchive`.

## Filtros

Os filtros são opcionais e podem ser enviados tanto para `gerar` quanto para `exportar`:

| Parâmetro | Formato | Aplicação |
| --- | --- | --- |
| `modalidade` | ID numérico | Alunos, presença e turmas. |
| `aluno` | ID numérico | Avaliações. |
| `turma` | ID numérico | Presença e treinos. |
| `cargo` | ID numérico | Funcionários. |
| `dataInicio` | `dd/mm/YYYY` ou `YYYY-mm-dd` | Presença, avaliações e treinos; inclui a partir da data informada. |
| `dataFim` | `dd/mm/YYYY` ou `YYYY-mm-dd` | Presença, avaliações e treinos; inclui o dia inteiro informado. |
| `status` | Ver tabela abaixo | Alunos, funcionários e treinos. |

Para `alunos` e `funcionarios`, `status` aceita `ativo` ou `inativo`. Para `treinos`, aceita `agendado`, `concluido` ou `cancelado`. Valores de status não reconhecidos são ignorados nessas consultas.

## Relatórios disponíveis

### Alunos

Retorna `id`, `nome`, `sobrenome`, `cpf`, `email`, `ativo`, `data_matricula`, `codigo_matricula`, `total_turmas` e `modalidades`. O filtro de modalidade considera as modalidades das turmas associadas ao aluno.

### Presença

Retorna `data_treino`, `turma`, `modalidade`, `aluno`, `situacao` e `checkin_time`. A situação representa o registro de presença, ausência ou justificativa.

### Avaliações

Retorna `id`, `data_avaliacao`, `peso`, `altura`, `imc`, `percentual_gordura`, `modalidade`, `aluno` e `avaliador`.

### Turmas

Considera somente turmas ativas e retorna `id`, `nome`, `modalidade`, `capacidade_maxima`, `instrutor`, `alunos` e `ocupacao`. `ocupacao` é calculada como o percentual de alunos ativos em relação à capacidade máxima.

### Funcionários

Retorna `nome`, `sobrenome`, `cpf`, `email`, `ativo`, `cargo`, `registro_profissional` e `treinos_realizados`. O total de treinos considera agendas concluídas atribuídas ao funcionário.

### Treinos

Retorna `data_hora_inicio`, `data_hora_fim`, `status`, `treino`, `turma`, `espaco` e `instrutor`.

## Componentes

### RelatorioController

Arquivo: [`RelatorioController.php`](../../api/src/relatorio/RelatorioController.php)

Responsável por:

- receber tipo, formato e filtros da requisição;
- responder métricas e resultados em JSON;
- iniciar downloads em CSV ou XLSX;
- mascarar CPFs antes da exportação;
- converter entradas inválidas em resposta HTTP `422`.

### RelatorioService

Arquivo: [`RelatorioService.php`](../../api/src/relatorio/RelatorioService.php)

Responsável por:

- validar o tipo solicitado;
- mapear o tipo para a consulta correspondente;
- orquestrar a chamada ao repositório;
- devolver o resultado com `tipo` e `registros`.

### RelatorioRepository

Arquivo: [`RelatorioRepository.php`](../../api/src/relatorio/RelatorioRepository.php)

Responsável por:

- executar as consultas agregadas e detalhadas;
- aplicar filtros de status, entidade e período;
- normalizar datas nos formatos aceitos;
- calcular taxa de presença e ocupação das turmas.

## Regras de negócio e privacidade

- O módulo não cria, altera ou exclui dados.
- Tipos de relatório fora da lista suportada geram erro `422` com a mensagem `Tipo de relatorio invalido.`.
- Formatos de exportação diferentes de `csv` e `xlsx` geram erro `422`.
- O CPF é mascarado nas exportações, preservando somente os quatro últimos dígitos.
- Filtros de data inválidos são ignorados pelo repositório; datas válidas podem ser informadas em formato brasileiro ou ISO.

## Relação com outros módulos

- `aluno`: dados cadastrais, matrícula e vínculos com turmas;
- `avaliacao`: avaliações físicas e avaliadores;
- `funcionario` e `cargo`: equipe, cargos e treinos realizados;
- `modalidade`: classificação de alunos, turmas e treinos;
- `turma`: capacidade, ocupação e presença;
- `treino`: agenda, locais, instrutores e status dos treinos.
