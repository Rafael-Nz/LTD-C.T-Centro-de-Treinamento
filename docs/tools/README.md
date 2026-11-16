# Ferramentas & Scripts do Sistema (CLI & Automação)

O diretório [`tools/`](../../tools/) contém scripts de linha de comando em PHP puro, rotinas utilitárias de banco de dados, testes automatizados e ferramentas para desenvolvimento e manutenção contínua do sistema, totalmente desacoplados de sistemas operacionais específicos.

---

## Organização das Ferramentas

```text
tools/
|-- financeiro/         # Rotinas de faturamento, estado JIT e integridade
|-- sistema/            # Ferramentas de validação de integridade e usuário mestre
|-- email/              # Compilação e templates de e-mail MJML (Node.js)
|-- testes/             # Suíte de testes automatizados (JIT, RBAC, e-mails, seed)
|-- desenvolvimento/    # Utilitários de migração SQL
|-- wiki/               # Sincronização automatizada da documentação com a Wiki Git
`-- download_all_plugins.php # Utilitário para recomposição e upgrade dos assets de UI
```

---

## 1. Operação Financeira & Automação Just-In-Time (JIT)

A operação financeira funciona de maneira nativa **Just-In-Time (JIT)** na API aliada a **triggers no MariaDB/MySQL** (`docs/sql/migrations/cobrancas_dinamicas_jit.sql`).

### Como Funciona a Execução JIT:
1. **Transparência na API e Painel:** Toda vez que um administrador ou aluno acessa rotas de contratos, cobranças ou métricas (`/api/financeiro/*`), o método `FinanceiroController::executarJit()` é acionado.
2. **Atualização Imediata em Eventos:** Ao cadastrar um novo contrato ou reativar um contrato pausado (`criarContrato()` e `statusContrato()`), a geração de faturas é executada instantaneamente via `gerarAutomaticasContrato()`.
3. **Triggers no Banco de Dados:** Triggers `BEFORE INSERT` e `BEFORE UPDATE` garantem que cobranças com vencimento anterior à data atual sejam marcadas como `vencida` automaticamente em tempo de gravação.
4. **Throttle Diário Inteligente:** Para evitar varreduras de contratos desnecessárias em múltiplos acessos sucessivos no mesmo dia, o sistema grava o carimbo diário em `tools/financeiro/financeiro_jit_state.json`. A sincronização de cobranças vencidas roda em tempo real (< 1ms), enquanto a varredura completa de contratos ativos roda no máximo uma vez ao dia.

### Componentes de `tools/financeiro/`:
- **`financeiro_jit_state.json`**: Guarda a data da última varredura diária (`ultima_execucao`), quantidade de cobranças geradas e vencidas atualizadas.
- **`gerar_cobrancas.php`**: Permite disparo manual via linha de comando ou integração com `cron` em servidores Linux de produção (mantido como fallback operacional).
- **`financeiro_status.php`**: Verifica a integridade do banco de dados e se as migrações financeiras foram aplicadas.

---

## 2. Administração, Segurança & Verificação

### Criação / Redefinição do Administrador Inicial:
- **`tools/sistema/criar_admin.php`**:
  Permite definir de forma interativa ou via parâmetros de linha de comando o e-mail e a senha mestra do primeiro administrador do sistema na instalação (ou redefinir a senha de uma conta existente), garantindo o cumprimento estrito das políticas de segurança (`PasswordPolicy`).
  
  ```bash
  # Modo interativo (solicita nome, e-mail e senha)
  php tools/sistema/criar_admin.php

  # Modo silencioso / parâmetros
  php tools/sistema/criar_admin.php --email="admin@meuclube.com" --senha="MinhaSenhaForte!123"

  # Redefinir a senha de um administrador já cadastrado
  php tools/sistema/criar_admin.php --reset
  ```

### Verificação da Cadeia Criptográfica de Auditoria:
- **`tools/sistema/verify_audit.php`**:
  Percorre todos os registros da tabela `audit_logs` desde o bloco gênesis até o topo da cadeia, recalculando os hashes SHA-256 encadeados e verificando se houve qualquer alteração ou adulteração manual no banco de dados.

  ```bash
  php tools/sistema/verify_audit.php
  ```
  Se a integridade estiver intacta, o script retorna código `0` com a confirmação da validação. Em caso de divergência de hash, o script aponta exatamente o ID da linha corrompida.

---

## 3. Templates de E-mail (MJML & Node.js)

O sistema utiliza a tecnologia **MJML** para compilar e-mails responsivos que são compatíveis com Gmail, Outlook e clientes mobile.

### Templates Disponíveis:
- `templates/account-activation.mjml`: E-mail de boas-vindas com link/token para ativação de conta.
- `templates/initial-password.mjml`: Envio de credenciais iniciais com código de matrícula e senha provisória gerada pelo sistema.
- `templates/password-reset.mjml`: Solicitação segura de recuperação e redefinição de senha.

### Comandos de Compilação:
```bash
# Instalar dependências Node.js
npm install --prefix tools/email

# Compilar todos os templates para HTML de produção em api/src/auth/
npm run email:build --prefix tools/email

# Modo observador durante a edição contínua do template (.mjml)
npm run email:watch --prefix tools/email
```

---

## 4. Suíte de Testes Automatizados

O projeto conta com testes unitários, testes de integração de regras de negócio e validação de triggers de banco.

### Execução dos Testes Rápidos:
```bash
composer test
```

### Testes Específicos:
- **Triggers JIT e Cache:**
  ```bash
  php tools/testes/financeiro/test_jit_triggers.php
  ```
- **RBAC e Isolamento de Sessão:**
  ```bash
  php tools/testes/rbac/test_session_guard_isolation.php
  php tools/testes/rbac/test_rbac_permissoes.php
  ```
- **Templates de E-mail:**
  ```bash
  php tools/testes/test_email_templates.php
  ```
- **Credenciais de Teste / Seeds:**
  ```bash
  php tools/testes/test_seed_logins.php
  ```

---

## 5. Gestão de Assets de Front-end & Utilitários de Desenvolvimento

A pasta [`plugins/`](../../plugins/) (contendo Bootstrap 5, DataTables 2, Phosphor Icons, FontAwesome 6, FullCalendar, Chart.js, Select2, SweetAlert2, etc.) **é versionada no Git**, garantindo compatibilidade entre versões de componentes JS/CSS e funcionamento offline imediato sem necessidade de downloads no setup.

- **`tools/download_all_plugins.php`**:
  Script CLI em PHP puro utilizado para manutenção e upgrade controlado das 44 dependências de front-end a partir de seus CDNs oficiais. Executável em qualquer sistema operacional (Windows, Linux, macOS).
  ```bash
  php tools/download_all_plugins.php
  ```
- **`tools/wiki/sincronizar_wiki.php`**:
  Script multiplataforma em PHP puro que sincroniza a pasta local `docs/` com o repositório Git Wiki do projeto (GitHub/GitLab):
  ```bash
  php tools/wiki/sincronizar_wiki.php ../ctt.wiki
  ```
