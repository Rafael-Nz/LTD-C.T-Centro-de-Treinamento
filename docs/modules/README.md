# Módulos da API (Domínios de Negócio)

Os módulos da API do **LTD-C.T** estão organizados por domínio de negócio no diretório [`api/src/`](../../api/src/). Cada módulo encapsula suas próprias rotas, controladores, serviços de aplicação, repositórios de dados e DTOs.

---

## 🏛️ Padrão Arquitetural dos Módulos

Todos os módulos de negócio seguem rigorosamente o mesmo padrão de camadas:

```text
api/src/<modulo>/
|-- <Modulo>Controller.php  # Recebe requisições HTTP, valida sessão e devolve JSON
|-- <Modulo>Service.php     # Concentra regras de negócio, transações e validações
|-- <Modulo>Repository.php  # Consultas SQL e persistência no banco de dados
|-- DTO/                    # Objetos tipados de transferência de dados
`-- Validation/             # Regras de validação específicas do módulo
```

---

## 📂 Catálogo de Módulos por Categoria

### 👥 1. Gestão de Pessoas & Acesso
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Auth** | Login, logout, recuperação e redefinição de senha, tokens e cookies de sessão | [auth.md](auth.md) |
| **Auth E-mail** | Integração SMTP com PHPMailer e compilação de e-mails responsivos MJML | [auth-email.md](auth-email.md) |
| **Usuário** | Consulta, atualização, ativação/desativação de credenciais de acesso | [usuario.md](usuario.md) |
| **Funcionário** | Gestão de colaboradores, instrutores e vínculo com cargos | [funcionario.md](funcionario.md) |
| **Cargo** | Cargos operacionais e atribuição de permissões granulares | [cargo.md](cargo.md) |

### 🏋️ 2. Operação & Treinamento
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Aluno** | Cadastro de alunos, geração atômica de matrícula e histórico | [aluno.md](aluno.md) |
| **Turma** | Criação de turmas, grade de horários, vagas e enturmação | [turma.md](turma.md) |
| **Treino** | Planejamento pedagógico de treinos, exercícios, WODs e chamada/presença | [treino.md](treino.md) |
| **Modalidade** | Cadastro de modalidades de treino (Cross, Musculação, Pilates, etc.) | [modalidade.md](modalidade.md) |
| **Local** | Controle de espaços físicos e capacidade de lotação | [local.md](local.md) |

### 🩺 3. Saúde & Acompanhamento Físico
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Anamnese** | Fichas de anamnese, questionários de saúde, histórico médico e alergias | [anamnese.md](anamnese.md) |
| **Avaliação** | Avaliações físicas antropométricas, bioimpedância, dobras e composição corporal | [avaliacao.md](avaliacao.md) |

### 💳 4. Gestão Financeira
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Financeiro** | Serviços, planos comerciais, contratos com snapshot de valores, cobranças automáticas com chave de idempotência, recebimentos, cancelamentos e estornos | [financeiro.md](financeiro.md) |

### 📊 5. Inteligência & Governança
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Relatórios** | Geração de relatórios operacionais, frequência e consolidações | [relatorio.md](relatorio.md) |
| **Auditoria** | Trilha imutável em blockchain com cadeia de hashes SHA-256 e triggers SQL | [auditoria.md](auditoria.md) |
| **Auditoria - Eventos** | Catálogo completo de eventos auditáveis e disparadores | [auditoria_eventos.md](auditoria_eventos.md) |
| **Auditoria - Operações**| Instalação de permissões estritas para `ctt_app` e checkpoints | [auditoria_operacoes.md](auditoria_operacoes.md) |

### 📖 6. Documentação Viva & Swagger
| Módulo | Descrição | Documentação |
| :--- | :--- | :--- |
| **Docs (Swagger)** | Interface Swagger UI navegável com injeção automática de CSRF e especificação OpenAPI 3.0 | [docs.md](docs.md) |

---

[← Voltar para a Página Inicial da Wiki](../Home.md)
