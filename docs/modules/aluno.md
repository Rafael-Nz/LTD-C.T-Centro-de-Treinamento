# Módulo Aluno

## Objetivo

Gerencia o cadastro, manutenção e acompanhamento dos alunos do centro de treinamento.

## Diretório

- `api/src/aluno`

## Rotas principais

- `GET /alunos`
- `GET /alunos/{id}`
- `POST /alunos`
- `PUT /alunos/{id}`
- `PUT /alunos/{id}/desativar`
- `PUT /alunos/{id}/reativar`
- `GET /alunos/{id}/avaliacoes`
- `POST /alunos/{id}/avaliacoes`

## Componentes

### AlunoController

Arquivo: `api/src/aluno/AlunoController.php`

Responsável por:

- listar alunos em páginas;
- excluir logicamente ou reativar registros;
- criar e editar alunos;
- delegar avaliações físicas pela rota relacionada.

### AlunoService

Arquivo: `api/src/aluno/AlunoService.php`

Responsável por:

- validar dados do cadastro do aluno;
- aplicar regra de negócio para criação/atualização;
- orquestrar relacionamento com matrículas e avaliações.

### AlunoRepository

Arquivo: `api/src/aluno/AlunoRepository.php`

Responsável por:

- persistência dos dados de aluno;
- consulta de alunos por ID ou filtros;
- status ativo/inativo;
- consulta de dados complementares necessários ao módulo.

### SequenciaMatriculaRepository

Arquivo: `api/src/aluno/SequenciaMatriculaRepository.php`

Responsável por:

- controlar a geração da sequência de matrícula do aluno;
- garantir unicidade e organização do número de matrícula.

### DTO

- `AlunoDTO`

## Entidades principais

- `aluno`
- `avaliacao_fisica` (através de rota específica)

## Regras de negócio

- O aluno é tratado com status ativo ou inativo.
- O cadastro pode ser associado a avaliações físicas.
- A matrícula pode seguir uma lógica sequencial e automatizada.
- Alunos e avaliações devem manter relacionamento confiável por `aluno_id`.

## Observação

As avaliações físicas do aluno são tratadas por um módulo específico, porém acessadas a partir do fluxo de aluno.
