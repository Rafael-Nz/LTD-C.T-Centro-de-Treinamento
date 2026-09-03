# Instalacao do projeto

Este guia possui dois cenarios de instalacao do LTD-C.T:

- **Desenvolvedor:** ambiente completo para alterar o sistema, o banco e os templates MJML.
- **Cliente final:** ambiente para executar o sistema, sem ferramentas de desenvolvimento.

Escolha o cenario adequado antes de iniciar.

## Instalacao para desenvolvedor

### Pre-requisitos

- Windows 10 ou superior.
- XAMPP com Apache, PHP e MySQL.
- Composer instalado e disponivel no PATH.
- Node.js e NPM instalados e disponiveis no PATH.
- Git, caso o projeto seja obtido por repositorio.

Confira as ferramentas no PowerShell:

```powershell
php -v
composer --version
npm --version
```

Se o Composer nao estiver disponivel, instale-o pelo instalador oficial e reinicie o terminal.

### Obter o projeto

Clone ou copie o projeto para a pasta publica do Apache:

```text
D:\xampp\htdocs\ctt
```

O endereco local esperado sera `http://localhost/ctt`.

### Iniciar o XAMPP

No painel do XAMPP, inicie Apache e MySQL. O Apache precisa atender a porta usada pelo endereco local.

### Criar o banco de dados

1. Abra o phpMyAdmin em `http://localhost/phpmyadmin`.
2. Abra [`sql/banco.sql`](sql/banco.sql).
3. Execute o script para criar o banco `db_centro_treinamento` e suas tabelas.

> Atenção: esse script começa com `DROP DATABASE` e apaga o banco inteiro. Use-o somente em uma instalação nova ou depois de realizar um backup. Para uma atualização de cliente com dados existentes, use uma migration específica fornecida pelo desenvolvedor.

A conexao padrao esta definida em [`api/core/Database/Database.php`](../api/core/Database/Database.php):

```text
Host: localhost
Porta: 3306
Banco: db_centro_treinamento
Usuario: root
Senha: vazia
```

### Instalar dependencias PHP

Na raiz do projeto, execute:

```powershell
cd D:\xampp\htdocs\ctt
composer install
```

Esse comando instala PHPMailer e Dotenv, alem de criar `vendor/autoload.php`.

### Configurar o ambiente

Crie `.env` a partir de `.env.example`:

```powershell
Copy-Item .env.example .env
```

Preencha as credenciais SMTP no `.env`:

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

Consulte [`modules/auth-email.md`](modules/auth-email.md) para detalhes do Gmail e do PHPMailer. O arquivo `.env` nao deve ser versionado.

### Compilar o e-mail MJML

Instale as dependencias JavaScript na raiz do projeto:

```powershell
npm install --prefix tools/email
```

O template editavel fica em [`tools/email/templates/password-reset.mjml`](../tools/email/templates/password-reset.mjml). Compile-o com:

```powershell
npm run email:build --prefix tools/email
```

Esse comando gera o HTML usado pelo PHPMailer em [`api/src/auth/password-reset.html`](../api/src/auth/password-reset.html).

Durante o desenvolvimento, use o modo de observacao para recompilar a cada alteracao:

```powershell
npm run email:watch --prefix tools/email
```

### Validar a instalacao

```powershell
& 'D:\xampp\php\php.exe' -l api/bootstrap.php
& 'D:\xampp\php\php.exe' -r "require 'vendor/autoload.php'; var_dump(class_exists('Dotenv\\Dotenv')); var_dump(class_exists('PHPMailer\\PHPMailer\\PHPMailer'));"
```

O lint deve informar que nao ha erros e as duas classes devem existir.

### Acessar a aplicacao

- Aplicacao: `http://localhost/ctt`
- Painel administrativo: `http://localhost/ctt/admin`
- API: `http://localhost/ctt/api`
- phpMyAdmin: `http://localhost/phpmyadmin`

## Instalacao para cliente final

### O que o cliente precisa

- Computador com Windows.
- XAMPP com Apache, PHP e MySQL.
- Acesso ao navegador.
- Um pacote da aplicacao preparado pelo desenvolvedor.

O cliente final nao precisa instalar Git, Composer, Node.js ou NPM. O pacote entregue deve conter as dependencias ja instaladas e os arquivos gerados previamente.

### Preparar o pacote

Antes de entregar o sistema, o desenvolvedor deve executar:

```powershell
composer install --no-dev --optimize-autoloader
npm install --prefix tools/email
npm run email:build --prefix tools/email
```

O pacote final deve incluir:

- `api/`, `admin/`, `public/` e `vendor/`;
- `composer.json` e `composer.lock`;
- `package.json` somente se for útil para controle da versão do projeto;
- `api/src/auth/password-reset.html` já compilado pelo MJML;
- `.env.example` como referência.

O pacote final nao deve incluir:

- `.env` com credenciais do desenvolvedor;
- `node_modules/`, salvo se o cliente também for desenvolver;
- senhas, tokens ou arquivos de teste desnecessarios.

### Instalar no computador do cliente

1. Instale o XAMPP.
2. Inicie Apache e MySQL.
3. Copie a aplicacao para `D:\xampp\htdocs\ctt`.
4. Abra o phpMyAdmin em `http://localhost/phpmyadmin`.
5. Em uma instalação nova, crie o banco executando [`sql/banco.sql`](sql/banco.sql). Se o banco já existir e tiver dados, não execute esse script: solicite uma migration ao responsável pelo sistema.
6. Copie `.env.example` para `.env`.
7. Preencha o `.env` com as configuracoes fornecidas pelo responsavel pelo sistema.
8. Acesse `http://localhost/ctt` pelo navegador.

O cliente final nao deve executar comandos NPM nem instalar Node.js. O arquivo HTML do e-mail ja deve estar compilado e ser usado pelo PHPMailer.

### Configuracao de e-mail no cliente

O `.env` do cliente deve usar a conta SMTP definida para a operacao:

```env
APP_URL=http://localhost/ctt
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=conta-do-sistema@gmail.com
MAIL_PASSWORD=senha-de-aplicativo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=conta-do-sistema@gmail.com
MAIL_FROM_NAME="Cross C.T"
```

Nunca entregue a senha SMTP dentro do codigo-fonte ou do `.env.example`. Consulte [`modules/auth-email.md`](modules/auth-email.md) para configurar o Gmail.

### Validacao no cliente

1. Confirme que Apache e MySQL estao ativos.
2. Acesse `http://localhost/ctt/admin/login`.
3. Abra a tela de recuperacao de senha.
4. Solicite um link usando um e-mail cadastrado.
5. Confirme o recebimento e o acesso ao link.

## Problemas comuns

### Apache ou MySQL nao inicia

Verifique se outro servico esta usando as portas 80, 443 ou 3306.

### Erro de autoload

Execute `composer install` na raiz do projeto e confirme se `vendor/autoload.php` foi criado.

### Erro de conexao com o banco

Confirme se o MySQL esta ativo e se os valores em [`Database.php`](../api/core/Database/Database.php) correspondem ao ambiente local.
