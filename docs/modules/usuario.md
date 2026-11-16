# Módulo Usuario

## Objetivo

Gerencia os usuários do sistema, incluindo dados pessoais, contatos, endereços, status ativo/inativo e autenticação.

## Diretório

- [`api/src/usuario`](../../api/src/usuario)

## Rotas principais

- `GET /usuarios`
- `GET /usuarios/{id}`
- `PUT /usuarios/{id}`
- `PUT /usuarios/{id}/desativar`
- `PUT /usuarios/{id}/reativar`

## Componentes

### UsuarioController

Arquivo: [`UsuarioController.php`](../../api/src/usuario/UsuarioController.php)

Responsável por:

- listar usuários com paginação e filtros;
- detalhar um usuário por ID;
- atualizar dados pessoais e de relacionamento;
- desativar/reativar usuários;
- responder em formato JSON padronizado.

### UsuarioService

Arquivo: [`UsuarioService.php`](../../api/src/usuario/UsuarioService.php)

Responsável por:

- centralizar regras de criação e atualização de usuários;
- validar atributos e relações com endereço/contatos;
- orquestrar operações de persistência.

### UsuarioRepository

Arquivo: [`UsuarioRepository.php`](../../api/src/usuario/UsuarioRepository.php)

Responsável por:

- consultar usuários com paginação (`findPaginated`, `countFiltered`, `countAll`);
- buscar usuário por ID;
- criar e atualizar registros em `usuario`;
- alternar o status `ativo`;
- buscar usuário por login para autenticação.

### DTOs

- [`UsuarioDTO.php`](../../api/src/usuario/DTO/UsuarioDTO.php)
- [`EnderecoDTO.php`](../../api/src/usuario/DTO/EnderecoDTO.php)
- [`ContatoDTO.php`](../../api/src/usuario/DTO/ContatoDTO.php)

Esses objetos representam a estrutura do usuário, do endereço e dos contatos e são convertidos automaticamente pelo `BaseDTO`.

## Regras de negócio

- Usuários podem ser ativados ou desativados sem exclusão física (soft delete / controle de status `ativo`).
- A autenticação usa `email` ou `cpf` como identificador de login no painel administrativo, e `email` ou `codigo_matricula` no portal do aluno.
- As senhas são armazenadas em hash seguro utilizando `password_hash()` com o algoritmo Argon2id (`PASSWORD_ARGON2ID`).
- O cadastro do usuário pode incluir endereço e contatos como dados relacionados.
- **Isolamento de Contas**: O sistema adota relação 1:1 entre a conta `usuario` e o respectivo perfil (`aluno` ou `funcionario`), sem compartilhamento de contas ou reutilização de credenciais entre papéis.
- **Unicidade de Identificadores**: `cpf` e `email` possuem índices únicos. Tentativas de duplicidade retornam HTTP 409 (Conflito).

## Padrão de acesso

Este módulo é protegido por middleware de autenticação (`AuthMiddleware`) nas rotas da API.
