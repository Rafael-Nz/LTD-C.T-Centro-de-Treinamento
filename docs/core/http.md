# Core: Http

Objetivo

Roteamento e abstração básica de controllers e respostas HTTP.

Arquivos principais

- [Router.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/Http/Router.php)
- [Controller.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/Http/Controller.php)

Router (Dispatcher)

- Define rotas por método: `get`, `post`, `put`, `delete` e suporta `group()` para prefixos.
- Converte definições com parâmetros tipo `/usuarios/{id}` em expressões regulares e faz o match com a URI.
- Executa middlewares associados antes de chamar o handler.
- Suporta handlers como string (callable) ou array `[ControllerClass, 'method']`.
- Em caso de rota não encontrada, retorna 404 com payload JSON padronizado.

Controller (Base)

- Conveniências para endpoints JSON:
  - `json($data, $status)` — resposta padrão com chave `success` e `data`.
  - `error($message, $status)` — resposta de erro com `success: false`.
  - `datatable($payload)` — envia payload para DataTables (JSON puro).
  - `only($method)` — força método HTTP.
  - `body()` — lê JSON do `php://input` e devolve array.
  - `input($key, $default)` — acessa $_POST/$_GET de forma unificada.
  - `auth()` — atalho para `Core\Auth\Auth::check()`.

Fluxo básico de uma requisição

1. bootstrap.php carrega as rotas e chama `$router->dispatch()`.
2. Router faz matching da rota e instancia middlewares.
3. Router constrói e chama o controller (ou callable) com parâmetros extraídos da URI.
4. Controller usa os helpers para validar método, ler corpo e retornar JSON.

Notas

- O Router usa expressões regulares para captura de parâmetros nomeados e passa apenas valores para o handler.
- Erros de controller (classe ou método inexistente) são tratados e retornam 500 com mensagem JSON.