# Core: Services

Objetivo

Fornecer uma base para classes de serviço de domínio com utilitários comuns (transações e validação).

Arquivo principal

- [Service.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/Services/Service.php)

Funcionalidades

- `transaction(callable $callback)` – executa um callback dentro de uma transação PDO, suportando savepoints quando já estiver em uma transação externa.
  - Se já houver uma transação, cria um savepoint para isolar a operação.
  - Em caso de exceção, faz rollback (ou rollback to savepoint) e relança a exceção.
- `validateData(array|object $data, array $rules, array $messages = [], array $attributes = [])` – delega ao `Core\Validation\Validator`.
- `validator()` – instância (lazy) do validador compartilhado.

Como usar

- Serviços de domínio (ex.: `UsuarioService`, `TurmaService`) estendem `Core\Services\Service`.
- Para operações compostas que precisam de atomicidade, envolver a lógica em `$this->transaction(function () { ... })`.
- Validar dados via `$this->validateData($dto, $rules)` antes de persistir.

Notas

- A responsabilidade de orquestrar repositórios e aplicar regras de negócio deve ficar nos Services, mantendo controllers finos.
