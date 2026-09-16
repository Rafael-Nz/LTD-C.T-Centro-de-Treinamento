# Instalação

Este guia instala os arquivos e as dependências do projeto. A configuração do banco, do `.env`, da auditoria e da rotina financeira está no [Setup da aplicação](setup.md).

## Requisitos

Para executar o sistema:

- Windows 10 ou superior;
- XAMPP com Apache, PHP 8.2+ e MySQL/MariaDB;
- aplicação em `D:\xampp\htdocs\ctt`.

Para desenvolvimento, também são necessários Git, Composer, Node.js e NPM.

## Instalação para desenvolvimento

1. Clone ou copie o projeto para:

   ```text
   D:\xampp\htdocs\ctt
   ```

2. Abra a pasta no VS Code e, no terminal, instale as dependências PHP:

   ```powershell
   cd D:\xampp\htdocs\ctt
   composer install
   ```

3. Instale as dependências do template de e-mail:

   ```powershell
   npm install --prefix tools/email
   npm run email:build --prefix tools/email
   ```

4. Inicie Apache e MySQL pelo painel do XAMPP.
5. Continue em [Setup da aplicação](setup.md).

O comando de compilação gera `api/src/auth/password-reset.html`. Durante alterações no template, use `npm run email:watch --prefix tools/email`.

## Preparação do pacote de produção

Antes de entregar o sistema:

```powershell
composer install --no-dev --optimize-autoloader
npm install --prefix tools/email
npm run email:build --prefix tools/email
```

Inclua no pacote:

- `admin/`, `api/`, `public/` e `vendor/`;
- `.htaccess`, `.env.example`, `composer.json` e `composer.lock`;
- `docs/setup.md` e `docs/sql/`;
- `tools/financeiro/` e `tools/sistema/`;
- `api/src/auth/password-reset.html` já compilado.

Não inclua:

- `.env`, senhas ou tokens;
- `.git/` e arquivos de log;
- `node_modules/`;
- `tools/testes/`, `tools/desenvolvimento/` ou fontes MJML, salvo quando o pacote também for usado para desenvolvimento.

O computador do cliente não precisa de Git, Composer, Node.js ou NPM quando o pacote já contém `vendor/` e o HTML de e-mail compilado.

## Problemas comuns

- **`vendor/autoload.php` ausente:** execute `composer install`.
- **Apache ou MySQL não inicia:** verifique conflitos nas portas 80, 443 e 3306.
- **PHP não encontrado no terminal:** use `D:\xampp\php\php.exe` ou adicione o PHP do XAMPP ao `PATH`.

Próximo passo: [configurar e ativar a aplicação](setup.md).
