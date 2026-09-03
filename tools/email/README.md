# Templates de e-mail

Esta pasta contém somente as ferramentas de desenvolvimento dos templates de e-mail.

## Estrutura

- `templates/password-reset.mjml`: fonte editável do e-mail de redefinição de senha.
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

O build gera `api/src/auth/password-reset.html`, que é carregado pelo PHPMailer.
