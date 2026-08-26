# Core: Auth

Objetivo

Fornecer utilitários de sessão e autenticação usados por toda a API.

Arquivos principais

- [Auth.php](../../api/core/Auth/Auth.php)
- [AuthMiddleware.php](../../api/core/Auth/AuthMiddleware.php)

Responsabilidades

- Gerenciar sessão PHP e informações do usuário (login, logout, id e dados básicos).
- Fornecer métodos estáticos convenientes: login(), logout(), id(), user(), check().
- Middleware simples que chama Auth::check() para proteger rotas.

Como usar

- Em controllers e serviços: chamar `\Core\Auth\Auth::id()` ou `Auth::user()` para obter contexto do usuário.
- Em rotas protegidas: adicionar `AuthMiddleware::class` para o array de middlewares na definição da rota (ver [api/routes/api.php](../../api/routes/api.php)).

Observações

- Auth::check() retorna uma resposta 401 e encerra a execução se a sessão não estiver válida.
- A implementação atual usa sessions PHP nativas e deve ser adaptada se for migrada para tokens JWT ou outro método sem estado.