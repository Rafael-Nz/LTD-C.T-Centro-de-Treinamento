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
2. Abra [`sql/setup/banco.sql`](sql/setup/banco.sql).
3. Execute o script com uma conta administrativa para criar o banco `db_centro_treinamento`, suas tabelas e os triggers de auditoria. Essa conta será o `DEFINER` dos triggers e deve permanecer disponível com os privilégios necessários.

> Atenção: esse script começa com `DROP DATABASE` e apaga o banco inteiro. Use-o somente em uma instalação nova ou depois de realizar um backup. Para uma atualização de cliente com dados existentes, use uma migration específica fornecida pelo desenvolvedor.

### Configurar a conta com permissões limitadas

A aplicação deve usar uma conta exclusiva `ctt_app`, inclusive no desenvolvimento. Reserve a conta administrativa para instalar o schema, manter os triggers e aplicar permissões. Não instale os triggers usando `ctt_app` como `DEFINER`.

1. No phpMyAdmin, conectado como administrador, execute o SQL abaixo. Substitua o marcador por uma senha forte gerada para esse ambiente:

   ```sql
   CREATE USER 'ctt_app'@'localhost' IDENTIFIED BY '<senha-forte-gerada-no-ambiente>';
   ```

2. Importe ou execute [`sql/auditoria_permissoes.sql`](sql/auditoria_permissoes.sql) com a conta administrativa. O arquivo revoga os grants anteriores de `ctt_app@localhost` e aplica as permissões por tabela e coluna. Use uma conta exclusiva, sem roles herdadas; não aplique esse procedimento a uma conta compartilhada.
3. Ao configurar o `.env` na etapa seguinte, informe a mesma senha definida no CREATE USER:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=db_centro_treinamento
DB_USERNAME=ctt_app
DB_PASSWORD="<senha-definida-no-CREATE-USER>"
```

Os marcadores entre `<...>` devem ser substituídos; não são senhas para uso real. Se a conta já existir, confirme que é exclusiva da aplicação e configure sua senha pelo administrador, sem executar novamente CREATE USER.

A conta recebe SELECT, INSERT, UPDATE e DELETE nas tabelas de negócio. Em `audit_logs`, recebe SELECT e INSERT somente nas colunas dos eventos enviadas pela API; snapshots, IDs e hashes são preenchidos pelos triggers. Em `audit_chain_state`, recebe apenas SELECT. Ela não pode alterar/apagar logs, modificar o `chain head`, remover triggers ou executar DROP/TRUNCATE.

O script usa o banco `db_centro_treinamento` e a conta `ctt_app@localhost`. Se o ambiente usar outro banco ou host de conexão, ajuste essas referências no SQL e no `.env`. Reaplique as permissões após recriar o schema e revise o script quando adicionar tabelas. Não conceda privilégios amplos sobre `banco.*`, pois isso removeria a restrição de acesso à auditoria.

Consulte [Operação da auditoria](modules/auditoria_operacoes.md) para os detalhes dos grants, do `DEFINER` e da verificação de integridade.

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

Preencha as credenciais do banco conforme a etapa anterior e as credenciais SMTP no `.env`:

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

Em produção, altere `APP_ENV` para `production` e configure `APP_ALLOWED_ORIGIN` com a origem exata da aplicação. Mantenha a conta restrita em `DB_USERNAME` e sua senha em `DB_PASSWORD`. Fora de `APP_ENV=development`, a conexão recusa o usuário `root` ou senha vazia.

As regras de senha, recuperação de acesso e limitação de tentativas estão documentadas em [`modules/auth.md`](modules/auth.md).

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

Valide também a conexão com a conta restrita e a integridade da auditoria:

```powershell
& 'D:\xampp\php\php.exe' tools/verify_audit.php
```

O verificador deve informar `OK: cadeia integra`. Uma instalação sem eventos pode retornar zero registros. Execute pelo terminal; o acesso HTTP a `/tools` é bloqueado.

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
- `.htaccess`, com as regras de roteamento e bloqueio de arquivos internos;
- `docs/sql/setup/banco.sql` e `docs/sql/auditoria_permissoes.sql`, para o responsável pela instalação;
- `tools/verify_audit.php`, para verificação pelo terminal.

O pacote final nao deve incluir:

- `.env` com credenciais do desenvolvedor;
- `node_modules/`, salvo se o cliente também for desenvolver;
- senhas, tokens ou arquivos de teste desnecessarios.

### Instalar no computador do cliente

1. Instale o XAMPP.
2. Inicie Apache e MySQL.
3. Copie a aplicacao para `D:\xampp\htdocs\ctt`.
4. Abra o phpMyAdmin em `http://localhost/phpmyadmin`.
5. Em uma instalação nova, crie o banco executando [`sql/setup/banco.sql`](sql/setup/banco.sql). Se o banco já existir e tiver dados, não execute esse script: solicite uma migration ao responsável pelo sistema.
6. Com a conta administrativa, siga [Configurar a conta com permissões limitadas](#configurar-a-conta-com-permissões-limitadas): crie uma conta exclusiva para esse ambiente e execute `sql/auditoria_permissoes.sql`. Não reutilize a senha do desenvolvedor.
7. Copie `.env.example` para `.env` e preencha `DB_USERNAME=ctt_app`, a senha criada, `APP_ENV=production` e as demais configurações fornecidas pelo responsável pelo sistema.
8. Execute `D:\xampp\php\php.exe tools/verify_audit.php` na raiz do projeto e confirme `OK: cadeia integra`.
9. Acesse `http://localhost/ctt` pelo navegador.

O cliente final nao deve executar comandos NPM nem instalar Node.js. O arquivo HTML do e-mail ja deve estar compilado e ser usado pelo PHPMailer.

Para bancos existentes, execute a migration [`sql/migrations/student_account_activations.sql`](sql/migrations/student_account_activations.sql). Novos alunos recebem um convite por e-mail para definir a própria senha; o link de ativação é de uso único e expira em 24 horas.

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

Confirme se o MySQL está ativo e se `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env` correspondem à conta criada. Não altere o código de `Database.php` para configurar credenciais.

Se aparecer `access denied` ou uma operação for recusada por falta de privilégios, confira o banco e o host da conta e reaplique `sql/auditoria_permissoes.sql` como administrador. Confirme também que os triggers foram instalados com um `DEFINER` administrativo válido. Não contorne o problema colocando root no `.env` ou concedendo privilégios amplos à aplicação.
