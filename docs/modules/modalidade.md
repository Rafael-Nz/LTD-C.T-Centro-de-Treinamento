# Módulo Modalidade

## Objetivo

Gerencia as modalidades de treinamentos e serviços ofertados pelo centro, como base para turma, treino e organização das atividades.

## Diretório

- `api/src/modalidade`

## Rotas principais

- `GET /modalidades`
- `GET /modalidades/{id}`
- `POST /modalidades`
- `PUT /modalidades/{id}`
- `PUT /modalidades/{id}/desativar`
- `PUT /modalidades/{id}/reativar`

## Componentes

### ModalidadeController

Arquivo: `api/src/modalidade/ModalidadeController.php`

Responsável por:

- receber e validar requisições da API;
- delegar criação, listagem e atualização ao serviço;
- responder em JSON para o frontend.

### ModalidadeService

Arquivo: `api/src/modalidade/ModalidadeService.php`

Responsável por:

- validar dados da modalidade;
- aplicar regras de criação e atualização;
- chamar o repositório para persistência.

### ModalidadeRepository

Arquivo: `api/src/modalidade/ModalidadeRepository.php`

Responsável por:

- consulta e escrita em `modalidade`;
- filtros e listagem de registros ativos/inativos;
- obtenção por ID.

### DTO

- `ModalidadeDTO`

## Entidades principais

- `modalidade`

## Regras de negócio

- Modalidades podem ser ativadas ou desativadas sem exclusão física.
- O módulo serve de base para organização de turmas e planejamento de treinos.

## Relação com outros módulos

- `turma`: as turmas pertencem a uma modalidade de ensino/treino;
- `treino`: os treinos podem estar relacionados a modalidades ou rotinas de formação.
