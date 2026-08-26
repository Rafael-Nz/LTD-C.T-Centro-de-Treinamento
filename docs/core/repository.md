# Core: Repository (padrão base)

Objetivo

Classe base para repositórios de domínio que encapsula operações comuns de banco de dados.

Arquivo principal

- [Repository.php](../../api/core/Database/Repository.php)

Responsabilidades

- Fornecer `$this->db` (PDO) para repositórios filhos via `Database::getConnection()`.
- Métodos utilitários:
  - `fetchAll(string $sql, array $params = [])` - retorna todas as linhas.
  - `fetch(string $sql, array $params = [])` - retorna uma linha ou null.
  - `execute(string $sql, array $params = [])` - executa e indica se linhas foram afetadas.
  - `lastInsertId()` - obtém o ID da última inserção.

Uso sugerido

- Cada módulo (ex.: `UsuarioRepository`, `AlunoRepository`) estende esta classe para aproveitar prepared statements e fetch mode padronizado.
- Mantenha SQL simples; para queries complexas extraia para métodos privados com nomes descritivos.

Exemplo rápido

- `UsuarioRepository::findByLogin($login)` chama `$this->fetch($sql, [$login, $login])` para evitar duplicação de lógica de execução.
