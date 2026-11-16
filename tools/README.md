# Ferramentas do projeto

Os comandos auxiliares ficam separados pela finalidade para manter a aplicação organizada, leve e 100% multiplataforma em qualquer ambiente com XAMPP (Windows, Linux e macOS).

## Operação financeira & Automação JIT

O sistema utiliza arquitetura **Just-In-Time (JIT)** aliada a triggers dinâmicas do banco de dados (`cobrancas_dinamicas_jit.sql`), gerando e atualizando faturas sob demanda diretamente na API. Todas as ferramentas legadas do Agendador do Windows foram expurgadas.

A pasta `tools/financeiro/` concentra scripts e arquivos de controle:

- `financeiro_bootstrap.php`: inicializa os comandos PHP executados pelo terminal;
- `financeiro_status.php`: verifica se o banco possui a estrutura financeira e migrações aplicadas;
- `gerar_cobrancas.php`: script CLI em PHP puro para geração manual sob demanda ou integração via cron em servidores Linux;
- `financeiro_jit_state.json`: arquivo de controle e cache/throttle diário da rotina Just-In-Time executada pela API.

## Administração e verificação do sistema

A pasta `tools/sistema/` concentra rotinas de inicialização e checagem:

- `criar_admin.php`: define de forma interativa ou via CLI o e-mail e a senha mestra do administrador inicial (ou redefine senha existente);
- `verify_audit.php`: valida a integridade da cadeia criptográfica de auditoria (blockchain interna SHA-256) usando a conta configurada no `.env`.

## Testes automatizados

`tools/testes/` contém a suíte completa de testes do sistema:

- **Segurança e RBAC, com banco e sessões em memória:**
  ```powershell
  php tools/testes/rbac/test_security.php
  ```
  Exige `pdo_sqlite`; cobre coexistência e revogação de guards, propriedade de cadastro, proteção do detalhe de turma, logout e renderização do painel, sem conectar ao banco configurado no `.env`.
- **Suite rápida:**
  ```powershell
  composer test
  ```
- **Testes de integração e triggers JIT:**
  - `tools/testes/financeiro/test_jit_triggers.php`: valida as triggers do MySQL e o throttle do cache JIT;
  - `tools/testes/financeiro/test_financeiro_rapido.php`: valida regras de negócio e cálculo de encargos;
  - `tools/testes/test_email_templates.php`: valida a compilação e substituição dos templates HTML de e-mail;
  - `tools/testes/test_seed_logins.php`: valida logins de teste com e-mail, CPF e matrícula;
  - `tools/testes/rbac/`: testes de isolamento de sessão, guards e controle de acesso RBAC.

## E-mails e templates (MJML)

`tools/email/` contém os fontes editáveis e dependências para compilar os e-mails responsivos via MJML:
- `templates/account-activation.mjml`: e-mail de ativação de conta;
- `templates/initial-password.mjml`: credenciais e matrícula do novo aluno;
- `templates/password-reset.mjml`: redefinição de senha com token seguro.

## Desenvolvimento & Assets

- `tools/download_all_plugins.php`: script multiplataforma em PHP puro para atualização e manutenção das bibliotecas offline em `plugins/`;
- `tools/desenvolvimento/build_financeiro_migration.php`: gerador de migração financeira para revisão no Git;
- `tools/wiki/sincronizar_wiki.php`: script PHP puro para sincronizar e publicar os arquivos em `docs/` para a Wiki oficial do repositório.
