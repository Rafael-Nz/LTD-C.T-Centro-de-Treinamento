# Setup inicial da aplicação

Execute este procedimento depois da [instalação dos arquivos e dependências](instalacao.md). Os comandos consideram o projeto em `D:\xampp\htdocs\ctt`.

## 1. Iniciar os serviços

No painel do XAMPP, inicie Apache e MySQL. Confirme o acesso ao phpMyAdmin em `http://localhost/phpmyadmin`.

## 2. Preparar o banco

Escolha apenas um cenário.

### Instalação nova

Execute [`sql/setup/banco.sql`](sql/setup/banco.sql) no phpMyAdmin usando uma conta administrativa. O script cria o banco `db_centro_treinamento`, todas as tabelas e os gatilhos de auditoria.

> O script começa com `DROP DATABASE`. Nunca o execute sobre uma base que contenha dados que precisam ser preservados.

## 3. Criar a conta da aplicação

Use uma conta administrativa para criar um usuário exclusivo e definir uma senha forte:

```sql
CREATE USER 'ctt_app'@'localhost' IDENTIFIED BY '<senha-forte>';
```

Se a conta já existir, ajuste-a com `ALTER USER` em vez de recriá-la. Depois, execute [`sql/auditoria_permissoes.sql`](sql/auditoria_permissoes.sql) como administrador.

A aplicação deve usar `ctt_app`; não use `root` e não conceda permissões amplas para contornar erros. Detalhes estão em [Operação da auditoria](modules/auditoria_operacoes.md).

## 4. Configurar o ambiente

Na raiz do projeto:

```powershell
Copy-Item .env.example .env
```

Preencha pelo menos:

```env
APP_URL=http://localhost/ctt
APP_ENV=production
APP_ALLOWED_ORIGIN=http://localhost

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=db_centro_treinamento
DB_USERNAME=ctt_app
DB_PASSWORD=<senha-forte>

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=conta-do-sistema@gmail.com
MAIL_PASSWORD=<senha-de-aplicativo>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=conta-do-sistema@gmail.com
MAIL_FROM_NAME="Cross C.T"
```

Em desenvolvimento, `APP_ENV` deve ser `development`. Nunca versione o `.env`. Consulte [Configuração de e-mail](modules/auth-email.md) para preparar o SMTP.


## 5. Ativar a operação financeira

Revise os contratos ativos e faça uma primeira geração controlada:

```powershell
& 'D:\xampp\php\php.exe' tools/financeiro/gerar_cobrancas.php
```

A rotina pode criar competências passadas ainda ausentes. Confira o resultado na tela de cobranças. Ela não movimenta dinheiro nem envia mensagens.

Instale ou atualize a tarefa diária das 06:00:

```powershell
& .\tools\financeiro\instalar_rotina_financeiro.ps1
```

Confirme no Agendador de Tarefas a tarefa `CTT - Gerar cobrancas`. O MySQL precisa estar ativo e o usuário configurado pela tarefa deve estar conectado. As execuções são registradas em `tools/financeiro/financeiro_rotina.log`.

## 7. Validar o acesso

1. Abra `http://localhost/ctt/admin/login`.
2. Entre com uma conta administrativa.
3. Confirme o acesso ao financeiro e à lista de cobranças.
4. Teste a recuperação de senha com um e-mail real cadastrado.
5. Confirme o recebimento do e-mail e o funcionamento do link.

## Atualizações futuras

Para atualizar uma instalação existente:

1. faça backup do banco e dos arquivos;
2. copie a nova versão sem substituir o `.env`;
3. execute `composer install --no-dev --optimize-autoloader` quando necessário;
4. aplique somente as migrations da versão;
5. reaplique `sql/auditoria_permissoes.sql` se houver novas tabelas ou permissões;
6. repita as validações de auditoria e financeiro;
7. execute novamente o instalador da rotina financeira para atualizar o caminho da tarefa.

## Diagnóstico rápido

- **Erro de conexão:** confira MySQL e as variáveis `DB_*` do `.env`.
- **Acesso negado:** reaplique `sql/auditoria_permissoes.sql` como administrador; não troque para `root`.
- **Financeiro desatualizado:** aplique a migration correspondente e execute `financeiro_status.php` novamente.
- **Cobranças não geradas:** confira a tarefa no Agendador, o MySQL e `tools/financeiro/financeiro_rotina.log`.
