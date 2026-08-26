# Módulo Local

## Objetivo

Gerencia os espaços físicos e locais de treino disponíveis para o centro de treinamento.

## Diretório

- [`api/src/local`](../../api/src/local)

## Rotas principais

- `GET /locais`
- `GET /locais/{id}`
- `POST /locais`
- `PUT /locais/{id}`
- `PUT /locais/{id}/desativar`
- `PUT /locais/{id}/reativar`

## Componentes

### LocalController

Arquivo: [`LocalController.php`](../../api/src/local/LocalController.php)

Responsável por:

- controlar o CRUD dos espaços físicos;
- receber e responder as requisições em JSON;
- aplicar autenticação nas rotas protegidas.

### LocalService

Arquivo: [`LocalService.php`](../../api/src/local/LocalService.php)

Responsável por:

- validar dados do local;
- orquestrar manutenção da entidade;
- aplicar regras de negócio específicas do ambiente de treino.

### LocalRepository

Arquivo: [`LocalRepository.php`](../../api/src/local/LocalRepository.php)

Responsável por:

- operações de leitura e gravação da tabela de espaços de treino;
- busca por ID e listagem;
- controle de status ativo/inativo.

### DTO

- [`LocalDTO.php`](../../api/src/local/DTO/LocalDTO.php)

## Regras de negócio

- Locais podem ser ativados ou desativados sem exclusão física.
- O cadastro é relevante para alocar treinos, turmas e eventos.

## Relação com outros módulos

- `turma`: pode indicar o local da turma;
- `treino`: recursos e espaço para organização dos treinos.
