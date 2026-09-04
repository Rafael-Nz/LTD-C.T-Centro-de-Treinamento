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
- criar e validar tokens de recuperação de senha;
- enviar o e-mail de recuperação por SMTP;
- aplicar a política de senha e os limites de tentativas.

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
- Senhas novas devem ter entre 12 e 128 caracteres e conter pelo menos um número, uma letra maiúscula, uma letra minúscula e um caractere especial. A regra é implementada por `Core\Auth\PasswordPolicy`.
- Tokens de recuperação são armazenados somente como hash, expiram em 15 minutos e são invalidados após o uso. A solicitação retorna uma mensagem genérica mesmo quando o e-mail não está cadastrado.
- São permitidas três solicitações de recuperação consecutivas por identificador e IP na janela de 15 minutos; novas solicitações ficam temporariamente bloqueadas ao atingir o limite.
- Após cinco falhas consecutivas de login para o mesmo identificador e IP na janela de 15 minutos, novas tentativas ficam bloqueadas temporariamente.
- Um login bem-sucedido limpa o controle de falhas do identificador e IP.

## Proteção das requisições

- As rotas autenticadas usam `AuthMiddleware`, que exige uma sessão válida.
- Em rotas autenticadas que alteram estado (`POST`, `PUT`, `PATCH` e `DELETE`), o cliente deve enviar o valor do cookie `CTT_CSRF_TOKEN` no cabeçalho `X-CSRF-Token`.
- O token CSRF é criado pelo bootstrap, fica associado à sessão e é validado com comparação segura. Ausência ou divergência retorna HTTP 419.
- As rotas públicas de autenticação não exigem sessão nem CSRF; o controle de abuso é feito pelos limites de login e recuperação descritos acima.
- Bloqueios por excesso de tentativas retornam HTTP 429.

## Fluxo de recuperação de senha

1. O cliente envia o e-mail para `POST /auth/recuperar-senha`.
2. O serviço registra a solicitação no controle de tentativas e, se o usuário existir e estiver ativo, gera um token aleatório e envia o link por SMTP.
3. O cliente envia o token, a nova senha e a confirmação para `POST /auth/redefinir-senha`.
4. O serviço valida a política de senha, verifica a validade do token, atualiza a senha com `PASSWORD_ARGON2ID` e marca o token como usado.

Falhas de limite retornam HTTP 429. Tokens inválidos, expirados ou já utilizados não podem redefinir a senha.

## Integração com o Core

O módulo usa a camada base `Core\Auth\Auth` para manter a sessão do usuário e `Core\Http\Controller` para padronizar respostas HTTP.

## Configuração de e-mail

A configuração do PHPMailer, SMTP e variáveis do ambiente está documentada em [`auth-email.md`](auth-email.md).
