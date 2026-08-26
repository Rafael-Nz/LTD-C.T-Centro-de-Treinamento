# Módulo Treino

## Objetivo

Gerencia os treinamentos, planos de aula e agenda de atividades disponíveis para o centro de treinamento.

## Diretório

- [`api/src/treino`](../../api/src/treino)

## Rotas principais

- `GET /treinos`
- `GET /treinos/{id}`
- `POST /treinos`
- `PUT /treinos/{id}`
- `PUT /treinos/{id}/reativar`
- `DELETE /treinos/{id}`

## Componentes

### TreinoController

Arquivo: [`TreinoController.php`](../../api/src/treino/TreinoController.php)

Responsável por:

- listar e detalhar treinos;
- criar e atualizar registros;
- cancelar e reativar treinamentos conforme fluxo do sistema.

### TreinoService

Arquivo: [`TreinoService.php`](../../api/src/treino/TreinoService.php)

Responsável por:

- validar dados do treino;
- aplicar regras para criação, cancelamento e reativação;
- manter consistência com cronograma e turma.

### TreinoRepository

Arquivo: [`TreinoRepository.php`](../../api/src/treino/TreinoRepository.php)

Responsável por:

- persistência de dados em `treino`;
- busca por ID e listagem;
- controle de status e exclusão lógica ou cancelamento.

### DTOs

- [`TreinoDTO.php`](../../api/src/treino/DTO/TreinoDTO.php)
- [`TreinoAgendaDTO.php`](../../api/src/treino/DTO/TreinoAgendaDTO.php)

## Regras de negócio

- Os treinos podem ser cancelados e posteriormente reativados.
- O módulo é o responsável pela estrutura do cronograma de atividades.
- O treino está diretamente relacionado à organização das turmas e à rotina operacional do centro.

## Relação com outros módulos

- `turma`: agenda e confirmação de treinos em turma;
- `local`: locais onde os treinos são realizados;
- `modalidade`: base da formação e modalidade do treinamento.
