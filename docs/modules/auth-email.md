# Configuracao de e-mail da autenticacao

## Objetivo

Esta configuracao prepara o modulo de autenticacao para enviar e-mails de recuperacao de senha por SMTP usando o PHPMailer. As variaveis de ambiente sao carregadas pelo `vlucas/phpdotenv`.

> A configuracao das bibliotecas e do ambiente nao substitui a implementacao do fluxo de tokens, envio e redefinicao de senha no `AuthService`.

## Dependencias

Na raiz do projeto, as dependencias sao declaradas em [`composer.json`](../../composer.json):

- `phpmailer/phpmailer`
- `vlucas/phpdotenv`

Para instalar ou atualizar as dependencias:

```powershell
composer install
```

O autoload gerado pelo Composer fica em `vendor/autoload.php`.

## Arquivos de ambiente

O arquivo `.env` deve ficar na raiz do projeto e nao deve ser versionado. Use [`.env.example`](../../.env.example) como modelo.

Configuracao local com Gmail:

```env
APP_URL=http://localhost/ctt

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-de-aplicativo
MAIL_ENCRYPTION=tls

MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="Cross C.T"
```

### Variaveis

| Variavel | Finalidade |
|---|---|
| `APP_URL` | URL usada para montar o link de redefinicao de senha. |
| `MAIL_HOST` | Servidor SMTP do provedor de e-mail. |
| `MAIL_PORT` | Porta SMTP. A porta `587` e usada com TLS. |
| `MAIL_USERNAME` | Conta autenticada no servidor SMTP. |
| `MAIL_PASSWORD` | Senha SMTP ou senha de aplicativo. |
| `MAIL_ENCRYPTION` | Criptografia da conexao SMTP, como `tls`. |
| `MAIL_FROM_ADDRESS` | Endereco exibido como remetente. |
| `MAIL_FROM_NAME` | Nome exibido como remetente. |

## Configuracao do Gmail

O Gmail exige uma senha de aplicativo para clientes SMTP:

1. Ative a verificacao em duas etapas na conta Google.
2. Abra a area de seguranca da conta.
3. Gere uma senha de aplicativo para o projeto.
4. Informe os caracteres gerados em `MAIL_PASSWORD`.

Use a mesma conta em `MAIL_USERNAME` e `MAIL_FROM_ADDRESS`. Nao use a senha normal da conta Google.

Configuracao padrao do Gmail:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## Carregamento das variaveis

O arquivo [`api/bootstrap.php`](../../api/bootstrap.php) carrega o autoload e o `.env` antes de executar as rotas:

```php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
```

No codigo PHP, as configuracoes podem ser acessadas por `$_ENV`:

```php
$host = $_ENV['MAIL_HOST'];
$port = (int) $_ENV['MAIL_PORT'];
$username = $_ENV['MAIL_USERNAME'];
$password = $_ENV['MAIL_PASSWORD'];
```

## Seguranca

- Nunca versione o arquivo `.env`.
- Nunca publique `MAIL_PASSWORD` ou outra credencial SMTP.
- Nao registre credenciais em logs.
- Em producao, use `APP_URL` com HTTPS.
- Para redefinicao de senha, use tokens aleatorios, com expiracao e uso unico.

## Validacao

Depois de instalar as dependencias, valide o bootstrap:

```powershell
& 'D:\xampp\php\php.exe' -l api/bootstrap.php
```

Tambem e possivel confirmar o carregamento das bibliotecas:

```powershell
& 'D:\xampp\php\php.exe' -r "require 'vendor/autoload.php'; var_dump(class_exists('Dotenv\\Dotenv')); var_dump(class_exists('PHPMailer\\PHPMailer\\PHPMailer'));"
```

As duas verificacoes devem retornar `true` ou indicar ausencia de erros de sintaxe.

## Solucao de problemas

### Erro de autenticacao SMTP

Confira o usuario, a senha de aplicativo, o host, a porta e a criptografia. No Gmail, confirme tambem se a verificacao em duas etapas esta ativa.

### Variaveis vazias

Confira se o `.env` esta na raiz do projeto e se `vendor/autoload.php` existe. Depois confirme se o `bootstrap.php` esta carregando o Dotenv antes das rotas.

### Link de recuperacao incorreto

Confira o valor de `APP_URL`. Em producao, substitua o endereco local pelo dominio real da aplicacao.
