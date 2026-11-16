# LTD-C.T - Centro de Treinamento

Sistema web completo para gestão operacional, pedagógica e financeira de um centro de treinamento físico, crossfit ou academia. O sistema unifica o controle de alunos, equipe, grade de horários, treinos, saúde/anamnese, avaliações físicas e faturamento em uma arquitetura PHP estruturada com separação entre Painel Administrativo, Portal do Aluno e API RESTful.

---

## Resumo do Projeto & Recursos

O sistema foi concebido para atender às rotinas diárias da equipe gestora e, simultaneamente, fornecer uma experiência moderna e intuitiva aos praticantes:

- **Autenticação Multi-Guard com Sessões Isoladas:** Separação estrita entre a área da equipe gestora (`GUARD_ADMIN`) e o ambiente dos alunos (`GUARD_ALUNO`).
- **Múltiplos Métodos de Acesso:** Login por **E-mail**, **CPF** (para administradores e colaboradores) ou **Código de Matrícula** atômico (para alunos).
- **Portal do Aluno Responsivo:** Dashboard pessoal, consulta de matrículas e planos vigentes, grade de horários, evolução antropométrica em avaliações físicas e histórico financeiro com emissão de comprovantes.
- **Painel Administrativo Completo:** Gestão de alunos, funcionários, cargos e permissões granulares (RBAC), turmas, treinos (WODs), chamadas de presença, locais, anamnese e relatórios gerenciais.
- **Módulo Financeiro Just-In-Time (JIT):** Planos, serviços, contratos com snapshot histórico, parcelamento, faturamento automatizado sob demanda (JIT) com throttle inteligente, triggers automáticas no banco para faturas vencidas, e cálculo dinâmico de encargos (multa contratual e juros diários). **Dispensa o uso de agendadores externos ou tarefas agendadas do SO.**
- **Auditoria Criptográfica Imutável:** Trilha de auditoria estilo blockchain com hashes encadeados SHA-256 e triggers SQL que impedem e denunciam qualquer adulteração manual no banco de dados.
- **Documentação OpenAPI & Swagger UI Integrada:** Especificação viva da API em `/ctt/api/docs` com suporte nativo a sessão, proteção automática em produção e testes diretos de endpoints.

---

## Interfaces do Sistema

### 1. Painel Administrativo (`/admin`)
Interface restrita a administradores e colaboradores autorizados:
- **Dashboard:** Indicadores em tempo real de matrículas ativas, frequência e faturamento;
- **Pessoas:** Gestão completa de Alunos, Funcionários e Cargos com permissões granulares;
- **Treinamento:** Montagem de Turmas, grade horária semanal, agendamento de Treinos e controle de presença;
- **Saúde:** Formulários dinâmicos de Anamnese e Avaliações Físicas antropométricas;
- **Financeiro:** Catálogo de Serviços e Planos, Gestão de Contratos, Cobranças, Recebimentos com chave de idempotência e cancelamento/estorno com justificativa auditada;
- **Relatórios:** Painéis analíticos e relatórios operacionais consolidados.

### 2. Portal do Aluno (`/`)
Interface moderna desenvolvida para smartphones, tablets e desktop:
- **Dashboard:** Próximos treinos agendados e avisos do centro de treinamento;
- **Dados Pessoais:** Consulta cadastral e informações de contato;
- **Matrícula:** Dados do contrato atual, plano vinculado e período de vigência;
- **Avaliações Físicas:** Consulta da evolução de dobras cutâneas, perímetros e composição corporal;
- **Horários:** Grade completa de turmas e modalidades disponíveis;
- **Financeiro:** Faturas abertas, histórico de pagamentos e visualização de recibos.

---

## Módulos da API RESTful (`/api`)

A API PHP está organizada por domínios de negócio seguindo o padrão em camadas (`Controller -> Service -> Repository -> Database`):

| Módulo | Finalidade |
| :--- | :--- |
| `auth` | Login multi-identificador (e-mail, CPF, matrícula), logout, recuperação e ativação de conta |
| `usuario` | Gestão de credenciais, ativação/desativação e políticas de senha forte |
| `aluno` | Cadastro de alunos, geração sequencial de matrícula e histórico |
| `funcionario`| Cadastro da equipe, instrutores e vínculo com cargos |
| `cargo` | Definição de cargos operacionais e permissões RBAC do sistema |
| `turma` | Turmas, modalidades, capacidade máxima de lotação e grade de horários |
| `treino` | Planejamento pedagógico de treinos, exercícios e chamada de presença |
| `modalidade` | Modalidades esportivas oferecidas (Cross, Funcional, Musculação, etc.) |
| `local` | Cadastro e controle de capacidade dos espaços físicos |
| `anamnese` | Questionários médicos, histórico de saúde e restrições |
| `avaliacao` | Avaliações antropométricas, bioimpedância e evolução física |
| `financeiro` | Serviços, planos, contratos, cobranças JIT, pagamentos e encargos moratórios |
| `relatorio` | Consolidações gerenciais, frequência e métricas financeiras |
| `docs` | Interface Swagger UI e especificação OpenAPI 3.0.3 |

---

## Estrutura do Projeto

```text
ctt/
|-- admin/          # Painel administrativo (rotas, views e controller)
|-- api/            # API RESTful em PHP
|   |-- core/       # Infraestrutura base (Auth multi-guard, Database, Router, DTO, Validation)
|   |-- routes/     # Mapeamento central de rotas da API
|   `-- src/        # Módulos por domínio de negócio
|-- docs/           # Documentação completa formatada para Wiki
|   |-- aluno/      # Manual do Portal do Aluno
|   |-- admin/      # Manual do Painel Administrativo
|   |-- core/       # Documentação da infraestrutura técnica
|   |-- modules/    # Manuais detalhados de cada módulo da API
|   |-- setup/      # Modelos de VirtualHost para Apache e Nginx (produção)
|   `-- sql/        # Scripts de banco (setup, migrations e testes)
|-- plugins/        # Bibliotecas de front-end offline (Bootstrap, DataTables, etc.)
|-- public/         # Portal do Aluno, roteador público e assets estáticos
|-- tools/          # Ferramentas CLI, testes e compiladores
|   |-- email/      # Templates de e-mail MJML e scripts npm
|   |-- financeiro/ # Estado do faturamento JIT, testes e utilitários
|   |-- sistema/    # Criador do admin mestre e verificador de auditoria
|   |-- testes/     # Suíte de testes automatizados (composer test)
|   `-- wiki/       # Utilitário de sincronização da documentação com o Git
|-- .env            # Configurações de ambiente locais
|-- .htaccess       # Regras de reescrita e proteção HTTP
|-- autoload.php    # Autoloader PSR-4 da aplicação
`-- composer.json   # Dependências de backend e suítes de teste
```

---

## Como Rodar o Projeto (Guia Rápido)

Para rodar a aplicação em um ambiente local com **XAMPP** (PHP 8.2+ e MySQL/MariaDB):

### 1. Clonar e Posicionar o Código
Posicione o projeto na pasta `htdocs` do seu XAMPP nomeando o diretório como `ctt` (ex: `C:\xampp\htdocs\ctt`).

### 2. Dependências e Configuração
Na raiz do projeto (`htdocs/ctt`), execute no terminal:
```powershell
# Instalar bibliotecas PHP (phpdotenv, phpmailer)
composer install

# Compilar os templates de e-mail MJML (Dev)
npm install --prefix tools/email
npm run email:build --prefix tools/email
```
*(Nota: A pasta de dependências visuais `plugins/` já vem integralmente versionada no repositório, garantindo estabilidade de versão e funcionamento 100% offline imediato no XAMPP).*

### 3. Banco de Dados e Segurança de Auditoria
Com o Apache e o MySQL iniciados no painel do XAMPP:
```powershell
# Importar o schema completo com gatilhos de integridade
mysql -u root < docs/sql/setup/banco.sql

# Criar a conta de serviço da aplicação e aplicar as permissões estritas
mysql -u root -e "CREATE USER 'ctt_app'@'localhost' IDENTIFIED BY 'sua_senha_forte';"
mysql -u root -e "source docs/sql/auditoria_permissoes.sql"
```

### 4. Configurar o `.env`
Copie o modelo `.env.example` para `.env` e configure suas credenciais de banco (`DB_USER=ctt_app`, `DB_PASS=sua_senha_forte`) e as configurações de e-mail SMTP.

### 5. Criar o Administrador Inicial
```powershell
php tools/sistema/criar_admin.php
```

### 6. Acessar o Sistema
- **Painel Administrativo:** [`http://localhost/ctt/admin/login`](http://localhost/ctt/admin/login)
- **Portal do Aluno:** [`http://localhost/ctt/login`](http://localhost/ctt/login)
- **Swagger UI da API:** [`http://localhost/ctt/api/docs`](http://localhost/ctt/api/docs)

> [!NOTE]
> **Faturamento Just-In-Time:** O sistema não necessita de agendador de tarefas no Windows nem de cron jobs obrigatórios. O faturamento e o status de faturas vencidas são sincronizados automaticamente sob demanda na própria API!

---

## Documentação Completa & Wiki

Consulte os guias aprofundados na pasta [`docs/`](docs/):

- **[Página Inicial da Wiki (Home)](docs/Home.md)**: Índice completo e mapa de navegação.
- **Implantação:** [Guia de Instalação](docs/instalacao.md) • [Setup Inicial e Banco](docs/setup.md) • [Configuração de E-mail](docs/modules/auth-email.md)
- **Arquitetura & Segurança:** [Arquitetura Geral](docs/arquitetura.md) • [Core da API](docs/core/README.md) • [Cadeia de Auditoria](docs/modules/auditoria.md)
- **Interfaces:** [Painel Administrativo](docs/admin/README.md) • [Portal do Aluno](docs/aluno/README.md)
- **Módulos:** [Financeiro JIT](docs/modules/financeiro.md) • [Alunos](docs/modules/aluno.md) • [Turmas](docs/modules/turma.md) • [Docs & Swagger](docs/modules/docs.md)
- **Ferramentas:** [CLI, Testes e Automações](docs/tools/README.md)
