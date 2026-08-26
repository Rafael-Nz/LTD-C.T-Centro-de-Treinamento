# Módulo Auth

## Objetivo

Responsável pela autenticação e autorização do acesso ao painel administrativo e às rotas protegidas da API.

## Diretório

- [`api/src/auth`](../../api/src/auth)

## Rotas principais

- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/recuperar-senha`
- `POST /auth/redefinir-senha`

## Componentes

### AuthController

Arquivo: [`AuthController.php`](../../api/src/auth/AuthController.php)

Responsável por:

- receber o payload JSON;
- verificar método HTTP com `only('POST')`;
- validar campos essenciais (`login` e `senha`);
- chamar o `AuthService`;
- retornar mensagens padronizadas em JSON.

### AuthService

Arquivo: [`AuthService.php`](../../api/src/auth/AuthService.php)

Responsável por:

- localizar o usuário pelo e-mail ou CPF em `UsuarioRepository`;
- verificar a senha com `password_verify()`;
- confirmar que o usuário está ativo;
- validar que o perfil seja `admin` ou `funcionario`;
- iniciar a sessão no `Core\Auth\Auth`.

### LoginDTO

Arquivo: [`LoginDTO.php`](../../api/src/auth/DTO/LoginDTO.php)

Estrutura de entrada para autenticação, contendo campos como `login` e `senha`.

## Fluxo de login

1. O cliente envia `login` e `senha`.
2. `AuthController` transforma o JSON em `LoginDTO`.
3. `AuthService` consulta o usuário no banco.
4. Se credenciais forem válidas, a sessão é iniciada com dados do usuário (`id`, `nome`, `tipo`).
5. O controller retorna uma resposta JSON com sucesso.

## Regras de negócio

- Somente usuários ativos podem realizar login.
- Acesso restrito a colaboradores (`admin` e `funcionario`).
- O login falha silenciosamente em casos de credenciais inválidas para não expor dados sensíveis.
- A recuperação de senha está preparada para evolução futura; a implementação atual marca TODOs para persistência de token e envio de e-mail.

## Integração com o Core

O módulo usa a camada base `Core\Auth\Auth` para manter a sessão do usuário e `Core\Http\Controller` para padronizar respostas HTTP.
