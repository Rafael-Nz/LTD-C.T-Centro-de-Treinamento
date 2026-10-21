# Templates de e-mail

Esta pasta contém somente as ferramentas de desenvolvimento dos templates de e-mail.

## Estrutura

- `templates/initial-password.mjml`: fonte editável do e-mail de senha inicial e credenciais de acesso (incluindo a matrícula).
- `templates/password-reset.mjml`: fonte editável do e-mail de redefinição de senha.
- `templates/account-activation.mjml`: fonte editável do e-mail de ativação de conta.
- `package.json`: scripts e dependência do MJML.
- `package-lock.json`: versões fixadas das dependências.
- `node_modules/`: dependências instaladas localmente, ignoradas pelo Git.

## Comandos

Execute a partir da raiz do projeto:

```powershell
npm install --prefix tools/email
npm run email:build --prefix tools/email
```

Para recompilar automaticamente durante a edição:

```powershell
npm run email:watch --prefix tools/email
```

O build gera os seguintes arquivos em `api/src/auth/`, carregados pelo PHPMailer:
- `api/src/auth/initial-password.html`
- `api/src/auth/password-reset.html`
- `api/src/auth/account-activation.html`
