# Ferramentas & Scripts do Sistema (CLI & Automação)

O diretório [`tools/`](../../tools/) contém scripts de linha de comando, rotinas agendadas para o sistema operacional, ferramentas de verificação de integridade e suítes de testes automatizados.

---

## 📂 Organização das Ferramentas

```text
tools/
|-- financeiro/         # Rotinas operacionais de cobrança e agendamento
|-- sistema/            # Ferramentas de validação e verificação de integridade
|-- email/              # Compilação e templates de e-mail MJML
|-- testes/             # Testes de integração e banco de dados isolado
|-- desenvolvimento/    # Utilitários auxiliares de desenvolvimento e migração
`-- wiki/               # Utilitários de sincronização e exportação da documentação
```

---

## 💰 1. Operação Financeira & Agendador de Tarefas

A rotina financeira é responsável por gerar novas faturas conforme os contratos ativos dos alunos e atualizar o status de faturas vencidas.

### Componentes:
- **`tools/financeiro/gerar_cobrancas.php`**: Script PHP executado via CLI que consulta contratos vigentes, calcula competências pendentes e grava as cobranças de forma idempotente.
- **`tools/financeiro/rotina_financeiro.ps1`**: Script PowerShell que executa o script PHP e direciona a saída para o arquivo de log local (`financeiro_rotina.log`).
- **`tools/financeiro/instalar_rotina_financeiro.ps1`**: Cria uma tarefa agendada no **Agendador de Tarefas do Windows** (Task Scheduler) chamada `CTT - Gerar cobrancas`, configurada para rodar diariamente às **06:00**.
- **`tools/financeiro/financeiro_status.php`**: Verifica se todas as colunas, chaves de idempotência e índices necessários existem no banco de dados antes da execução.

### Instalação no Servidor / XAMPP:
```powershell
# Verificar o status das migrações financeiras
& 'D:\xampp\php\php.exe' tools/financeiro/financeiro_status.php

# Fazer a primeira execução manual
& 'D:\xampp\php\php.exe' tools/financeiro/gerar_cobrancas.php

# Instalar a tarefa diária no Windows
& .\tools\financeiro\instalar_rotina_financeiro.ps1
```

---

## 🛡️ 2. Administração, Segurança & Verificação

### Criação / Redefinição do Administrador Inicial:
- **`tools/sistema/criar_admin.php`** (e wrapper **`tools/sistema/criar_admin.ps1`**):
  Permite definir de forma interativa ou via parâmetros de linha de comando o e-mail e a senha mestra do primeiro administrador do sistema na instalação (ou redefinir a senha de uma conta existente), garantindo o cumprimento estrito das políticas de segurança (`PasswordPolicy`).
  
  ```powershell
  # Modo interativo (solicita nome, e-mail e senha com máscara)
  & .\tools\sistema\criar_admin.ps1

  # Modo silencioso / automação
  & .\tools\sistema\criar_admin.ps1 -Email "admin@meuclube.com" -Senha "MinhaSenhaForte!123"

  # Redefinir a senha de um administrador já cadastrado
  & .\tools\sistema\criar_admin.ps1 -Reset
  ```

### Verificação da Cadeia Criptográfica de Auditoria:
- **`tools/sistema/verify_audit.php`**:
  Percorre todos os registros da tabela `audit_logs` desde o bloco gênesis até o topo da cadeia, recalculando os hashes SHA-256 e verificando se houve qualquer adulteração manual no banco de dados.

  ```powershell
  & 'D:\xampp\php\php.exe' tools/sistema/verify_audit.php
  ```
  Se a integridade estiver intacta, o script retorna código `0` com a confirmação da validação. Em caso de divergência de hash, o script aponta exatamente o ID da linha corrompida.

---

## 📧 3. Templates de E-mail (MJML & Node.js)

O sistema utiliza a tecnologia **MJML** para gerar e-mails responsivos que são compatíveis com Gmail, Outlook e clientes mobile.

### Comandos:
```powershell
# Instalar dependências Node.js
npm install --prefix tools/email

# Compilar o template de redefinição de senha para HTML de produção
npm run email:build --prefix tools/email

# Modo observador durante a edição do template (.mjml)
npm run email:watch --prefix tools/email
```
O arquivo final compilado é salvo automaticamente em:
`api/src/auth/password-reset.html`

---

## 🧪 4. Suíte de Testes Automatizados

O projeto conta com testes unitários e de integração configurados via Composer e PHPUnit.

### Execução dos Testes Rápidos:
```powershell
composer test
```
Este comando executa a suíte rápida de validação de regras de negócio em memória sem depender do banco MySQL.
