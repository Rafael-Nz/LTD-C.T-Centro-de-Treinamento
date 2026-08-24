# Módulo Avaliacao

## Objetivo

Gerencia avaliações físicas dos alunos, registrando indicadores de desempenho, composição corporal e evolução dos treinamentos.

## Diretório

- `api/src/avaliacao`

## Rotas principais

- `GET /alunos/{id}/avaliacoes`
- `POST /alunos/{id}/avaliacoes`
- `GET /avaliacoes/{id}`
- `PUT /avaliacoes/{id}`

## Componentes

### AvaliacaoFisicaController

Arquivo: `api/src/avaliacao/AvaliacaoFisicaController.php`

Responsável por:

- listar avaliações de um aluno;
- criar uma nova avaliação para um aluno;
- buscar e atualizar uma avaliação específica.

### AvaliacaoFisicaService

Arquivo: `api/src/avaliacao/AvaliacaoFisicaService.php`

Responsável por:

- validar os dados da avaliação;
- aplicar regras empresariais sobre registro e atualização;
- orquestrar a persistência da avaliação do aluno.

### AvaliacaoFisicaRepository

Arquivo: `api/src/avaliacao/AvaliacaoFisicaRepository.php`

Responsável por:

- consultar avaliações por aluno;
- persistir nova avaliação;
- buscar e alterar registro específico em `avaliacao_fisica`.

### DTO

- `AvaliacaoFisicaDTO`

## Entidades principais

- `avaliacao_fisica`

## Regras de negócio

- A avaliação física está vinculada a um aluno específico.
- O módulo permite histórico de evolução por aluno.
- Há diferenciação entre registro inicial e atualização posterior da avaliação.

## Relação com outros módulos

- `aluno`: o fluxo de avaliações é acessado a partir do aluno;
- `treino`: os indicadores podem ser usados para acompanhamento do progresso do treino.
