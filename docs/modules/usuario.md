# Módulo Usuario

## Objetivo

Gerencia os usuários do sistema, incluindo dados pessoais, contatos, endereços, status ativo/inativo e autenticação.

## Diretório

- `api/src/usuario`

## Rotas principais

- `GET /usuarios`
- `GET /usuarios/{id}`
- `PUT /usuarios/{id}`
- `PUT /usuarios/{id}/desativar`
- `PUT /usuarios/{id}/reativar`

## Componentes

### UsuarioController

Arquivo: `api/src/usuario/UsuarioController.php`

Responsável por:

- listar usuários com paginação e filtros;
- detalhar um usuário por ID;
- atualizar dados pessoais e de relacionamento;
- desativar/reativar usuários;
- responder em formato JSON padronizado.

### UsuarioService

Arquivo: `api/src/usuario/UsuarioService.php`

Responsável por:

- centralizar regras de criação e atualização de usuários;
- validar atributos e relações com endereço/contatos;
- orquestrar operações de persistência.

### UsuarioRepository

Arquivo: `api/src/usuario/UsuarioRepository.php`

Responsável por:

- consultar usuários com paginação (`findPaginated`, `countFiltered`, `countAll`);
- buscar usuário por ID;
- criar e atualizar registros em `usuario`;
- alternar o status `ativo`;
- buscar usuário por login para autenticação.

### DTOs

- `UsuarioDTO`
- `EnderecoDTO`
- `ContatoDTO`

Esses objetos representam a estrutura do usuário, do endereço e dos contatos e são convertidos automaticamete pelo `BaseDTO`.

## Entidades principais

- `usuario`
- `endereco`
- `contato`

## Regras de negócio

- Usuários podem ser ativados ou desativados sem exclusão física.
- A autenticação usa `email` ou `cpf` como identificador de login.
- A senha é armazenada em hash com `password_hash()`.
- O cadastro do usuário pode incluir endereço e contatos como dados relacionados.

## Padrão de acesso

Este módulo é protegido por middleware de autenticação (`AuthMiddleware`) nas rotas da API.
