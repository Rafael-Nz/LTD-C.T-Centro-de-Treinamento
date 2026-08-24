# Módulo Cargo

## Objetivo

Gerencia os cargos utilizados no centro de treinamento, servindo como base para organização funcional e permissões internas.

## Diretório

- `api/src/cargo`

## Rotas principais

- `GET /cargos`
- `GET /cargos/{id}`
- `POST /cargos`
- `PUT /cargos/{id}`
- `PUT /cargos/{id}/desativar`
- `PUT /cargos/{id}/reativar`

## Componentes

### CargoController

Arquivo: `api/src/cargo/CargoController.php`

Responsável por:

- listar cargos;
- cadastrar e alterar cargos;
- desativar e reativar registro sem exclusão física.

### CargoService

Arquivo: `api/src/cargo/CargoService.php`

Responsável por:

- validar nomes e dados do cargo;
- aplicar regras de negócio para manutenção do cadastro;
- orquestrar chamadas ao repositório.

### CargoRepository

Arquivo: `api/src/cargo/CargoRepository.php`

Responsável por:

- operações de leitura e escrita na tabela `cargo`;
- consultas por ID, listagem e filtros;
- gerenciamento de status.

### DTO

- `CargoDTO`

## Entidades principais

- `cargo`

## Regras de negócio

- Os cargos podem ser ativados ou desativados sem remoção do registro.
- A manutenção desse módulo impacta a organização dos funcionários e permissões internas.
- O nome do cargo deve ser único ou validado conforme regra aplicada entre as regras de negócio do sistema.

## Uso no sistema

O cargo geralmente é usado como referência para:

- classificação de funcionários;
- controle de papéis no painel administrativo;
- organização de estrutura da operação.
