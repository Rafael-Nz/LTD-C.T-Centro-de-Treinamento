# LTD-C.T - Centro de Treinamento

Sistema web para gerenciamento de um centro de treinamento, com foco no controle administrativo da operação, cadastro de alunos e funcionários, organização de turmas, agenda de treinos e acompanhamento de informações de saúde por meio de anamnese.

O projeto está organizado em uma aplicação PHP com painel administrativo próprio, API modularizada e banco de dados MySQL. A proposta do sistema é centralizar as rotinas de gestão do centro de treinamento em uma única base, facilitando o controle de usuários, turmas, espaços e processos internos.

## Resumo do projeto

O sistema foi desenvolvido para apoiar a administração de um centro de treinamento físico. Atualmente, a base do projeto contempla:

- autenticação de usuários e controle de sessão no painel administrativo;
- cadastro e manutenção de alunos;
- cadastro e manutenção de funcionários e cargos;
- gerenciamento de turmas e relacionamento com alunos;
- organização de treinos e agenda de atividades;
- cadastro de locais/espacos de treino;
- registro de anamnese para acompanhamento inicial e histórico do aluno;
- estrutura para relatórios, configurações e perfil do usuário.

## Características do sistema

### Painel administrativo

O painel em `admin/` concentra a navegação operacional do sistema e possui rotas protegidas por sessão para perfis autorizados, como `admin` e `funcionario`.

Entre as telas já existentes no projeto estão:

- dashboard inicial;
- login, recuperação e redefinição de senha;
- listagem e formulário de alunos;
- listagem e formulário de funcionários;
- listagem e formulário de cargos;
- listagem e formulário de turmas;
- gerenciamento de alunos por turma;
- listagem e formulário de treinos;
- listagem e formulário de locais;
- avaliações;
- relatórios;
- configurações;
- perfil do usuário

### API própria em PHP

A pasta `api/` implementa a camada de serviços do sistema, com rotas organizadas por domínio e separação entre controller, service, repository e DTO.

Módulos identificados na API:

- `auth`: login, logout e recuperação/redefinição de senha;
- `usuario`: consulta, atualização, ativação e desativação de usuários;
- `aluno`: cadastro, edição e controle de status dos alunos;
- `funcionario`: cadastro, edição e controle de status dos funcionários;
- `cargo`: manutenção dos cargos utilizados no sistema;
- `turma`: gerenciamento das turmas;
- `treino`: agendamento e atualização de treinos;
- `local`: controle de locais de treino;
- `anamnese`: formulários, perguntas e respostas de anamnese.

### Modelagem de dados

Os scripts SQL em `docs/sql/` mostram que o sistema foi modelado para atender uma rotina administrativa relativamente completa, incluindo entidades como:

- `usuario`, `aluno` e `funcionario`;
- `cargo` e `permissao`;
- `contato` e `endereco`;
- `modalidade`, `turma` e `espaco_treino`;
- `treino_agenda` e `presenca_treino`;
- `anamnese_formulario`, `anamnese_pergunta`, `anamnese_opcao` e `anamnese_resposta`;
- `avaliacao_fisica`;
- `sistema_log`.

## Estrutura do projeto

```text
ctt/
|-- admin/          # painel administrativo, rotas e views
|-- api/            # API PHP, regras de negocio e acesso a dados
|   |-- core/       # infraestrutura base (router, auth, database, traits)
|   |-- routes/     # definicao central das rotas da API
|   `-- src/        # modulos por dominio (controller/service/repository/DTO)
|-- docs/
|   `-- sql/        # scripts SQL e estrutura inicial do banco
|-- public/         # assets publicos (CSS, JS, imagens)
|-- index.php       # ponto de entrada da aplicacao principal
`-- .htaccess       # reescrita de rotas para Apache/XAMPP
```

## Organização técnica

- O projeto utiliza PHP puro com roteamento customizado.
- O ambiente está preparado para execução local via Apache/XAMPP.
- A aplicação principal usa reescrita de URL pela raiz do projeto.
- O painel administrativo possui roteador próprio em `admin/router.php`.
- A API possui bootstrap dedicado em `api/bootstrap.php`.
- A conexão com banco utiliza PDO e banco MySQL `db_centro_treinamento`.
- A estrutura da API segue uma divisão por responsabilidade para facilitar manutenção e evolução.