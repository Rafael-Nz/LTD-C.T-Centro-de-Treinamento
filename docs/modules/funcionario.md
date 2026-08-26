# Módulo Funcionario

## Objetivo

Gerencia o cadastro e manutenção dos funcionários da organização, incluindo dados cadastrais e vínculo com cargo.

## Diretório

- [`api/src/funcionario`](../../api/src/funcionario)

## Rotas principais

- `GET /funcionarios`
- `GET /funcionarios/{id}`
- `POST /funcionarios`
- `PUT /funcionarios/{id}`
- `PUT /funcionarios/{id}/desativar`
- `PUT /funcionarios/{id}/reativar`

## Componentes

### FuncionarioController

Arquivo: [`FuncionarioController.php`](../../api/src/funcionario/FuncionarioController.php)

Responsável por:

- receber a requisição e delegar ao serviço;
- tratar retorno em JSON;
- aplicar regras de acesso para usuários autenticados.

### FuncionarioService

Arquivo: [`FuncionarioService.php`](../../api/src/funcionario/FuncionarioService.php)

Responsável por:

- validar cadastro e atualização do funcionário;
- aplicar regras específicas de acordo com contrato e permissões;
- manter consistência com usuário e cargo.

### FuncionarioRepository

Arquivo: [`FuncionarioRepository.php`](../../api/src/funcionario/FuncionarioRepository.php)

Responsável por:

- pesquisa e persistência de dados de funcionários;
- relacionamento com tabelas relacionadas, como `usuario` e `cargo`.

### DTO

- [`FuncionarioDTO.php`](../../api/src/funcionario/DTO/FuncionarioDTO.php)

## Regras de negócio

- O funcionário pode ser tratado com status ativo/inativo.
- O módulo se conecta ao perfil de usuário para permitir acesso ao sistema administrativo.
- O vínculo com cargo é importante para separar funções e permissões de operação.

## Relação com outros módulos

- `usuario`: base de autenticação e dados pessoais;
- `cargo`: classificação e permissões de função;
- `auth`: autenticação de acesso ao sistema.
