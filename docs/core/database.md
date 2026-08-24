# Core: Database

Objetivo

Gerenciar a conexão com o banco e configurar opções PDO compartilhadas.

Arquivos principais

- [Database.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/Database/Database.php)
- [Repository.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/Database/Repository.php)

Database

- Implementa um singleton para `PDO` com configuração de host, porta, nome do banco, charset e opções.
- Ajusta `ATTR_ERRMODE`, `ATTR_DEFAULT_FETCH_MODE`, `ATTR_EMULATE_PREPARES` e outras opções úteis.
- Executa comandos iniciais como `SET NAMES` e timezone SQL.
- Método utilitário `now()` que retorna `SELECT NOW()` do servidor de banco.
- Em erro de conexão, faz `error_log()` e relança a exceção.

Repository (base)

- Classe abstrata que injeta a conexão PDO (`$this->db = Database::getConnection()`).
- Fornece operações utilitárias: `fetchAll()`, `fetch()`, `execute()`, `lastInsertId()` e `prepareAndExecute()` (internal).
- Centraliza tratamento de prepared statements e fetch mode para evitar duplicação em repositórios de domínio.

Boas práticas

- Repositórios de domínio devem estender `Core\Database\Repository` e usar os métodos protegidos para executar consultas.
- Manter queries parametrizadas para evitar SQL injection (o Repository já utiliza prepared statements).
- Use `Service::transaction()` (veja Services) para agrupar operações que precisam de atomicidade.
