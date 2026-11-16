# Módulo Aluno

## Objetivo

Gerencia o cadastro, manutenção e acompanhamento dos alunos do centro de treinamento.

## Diretório

- [`api/src/aluno`](../../api/src/aluno)

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

Arquivo: [`AlunoController.php`](../../api/src/aluno/AlunoController.php)

Responsável por:

- listar alunos em páginas;
- excluir logicamente ou reativar registros;
- criar e editar alunos;
- delegar avaliações físicas pela rota relacionada.

### AlunoService

Arquivo: [`AlunoService.php`](../../api/src/aluno/AlunoService.php)

Responsável por:

- validar dados do cadastro do aluno;
- aplicar regra de negócio para criação/atualização;
- orquestrar relacionamento com matrículas e avaliações.

### AlunoRepository

Arquivo: [`AlunoRepository.php`](../../api/src/aluno/AlunoRepository.php)

Responsável por:

- persistência dos dados de aluno;
- consulta de alunos por ID ou filtros;
- status ativo/inativo;
- consulta de dados complementares necessários ao módulo.

### SequenciaMatriculaRepository

Arquivo: [`SequenciaMatriculaRepository.php`](../../api/src/aluno/SequenciaMatriculaRepository.php)

Responsável por:

- controlar a geração da sequência de matrícula do aluno;
- garantir unicidade e organização do número de matrícula.

### DTO

- [`AlunoDTO.php`](../../api/src/aluno/DTO/AlunoDTO.php)

## Regras de negócio

- O aluno é tratado com status ativo ou inativo.
- O cadastro pode ser associado a avaliações físicas.
- A matrícula segue lógica sequencial automatizada.
- Alunos e avaliações mantêm relacionamento confiável por `aluno_id`.
- **Criação de Conta Independente**: Todo cadastro cria uma nova conta exclusiva (`tipo_usuario = 'aluno'`). O sistema não reutiliza contas pré-existentes.
- **Credenciais**: A senha inicial é gerada automaticamente pelo sistema e enviada por e-mail para ativação.
- **Unicidade**: Tentativas de cadastrar CPF ou e-mail já existentes retornam erro HTTP 409 (Conflito).

## Observação

As avaliações físicas do aluno são tratadas por um módulo específico, porém acessadas a partir do fluxo de aluno.
