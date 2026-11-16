# Core: Auth

Objetivo

Fornecer utilitários de sessão e autenticação usados por toda a API.

Arquivos principais

- [Auth.php](../../api/core/Auth/Auth.php)
- [AuthMiddleware.php](../../api/core/Auth/AuthMiddleware.php)
- [Csrf.php](../../api/core/Auth/Csrf.php)
- [PasswordPolicy.php](../../api/core/Auth/PasswordPolicy.php)
- [AuthRateLimitException.php](../../api/core/Auth/AuthRateLimitException.php)

Responsabilidades

- Gerenciar sessão PHP e informações do usuário (login, logout, id e dados básicos).
- Fornecer métodos estáticos convenientes: login(), logout(), id(), user(), check().
- Proteger rotas com sessão válida e validação CSRF para métodos que alteram estado.
- Centralizar a política de senha e representar bloqueios temporários de autenticação.

Como usar

- Em controllers e serviços: chamar `\Core\Auth\Auth::id()` ou `Auth::user()` para obter contexto do usuário.
- Em rotas protegidas: adicionar `AuthMiddleware::class` para o array de middlewares na definição da rota (ver [api/routes/api.php](../../api/routes/api.php)).
- Em requisições protegidas `POST`, `PUT`, `PATCH` e `DELETE`: enviar o valor do cookie `CTT_CSRF_TOKEN` no cabeçalho `X-CSRF-Token`.

## Sessão e CSRF

- `Auth::start()` usa a sessão PHP `CTTSESSID` com cookies, modo estrito, `HttpOnly` e `SameSite=Lax`. O atributo `Secure` é ativado quando a requisição usa HTTPS.
- `Auth::login()` regenera o identificador da sessão antes de armazenar os dados do usuário.
- Cada guard fica vinculado ao hash da senha do perfil autenticado. Na primeira consulta de identidade de cada requisição, a conta é revalidada no banco e cargo/permissões são recarregados. Desativação, exclusão de perfil e mudança de senha invalidam o guard; remoção de permissão passa a valer na próxima requisição. O outro guard válido permanece ativo.
- Sessões anteriores à inclusão desse vínculo precisam autenticar novamente. O espelho legado na raiz de `$_SESSION` não concede autorização. Não há migração de banco para esta mudança.
- Em rotas compartilhadas, `X-Auth-Area` seleciona um único guard; a autorização e a verificação de propriedade usam essa mesma identidade. Um guard ausente não utiliza a sessão da outra área como alternativa. Rotas administrativas usam explicitamente o guard `admin`.
- O detalhe `GET /turmas/{id}`, que inclui integrantes e contatos, exige `turmas.visualizar` na área administrativa. O aluno continua usando a listagem de horários e seus próprios vínculos.
- O bootstrap chama `Csrf::token()` em toda requisição. O método cria o token na sessão quando necessário e o expõe no cookie `CTT_CSRF_TOKEN` para que o cliente possa enviá-lo no cabeçalho `X-CSRF-Token`.
- `AuthMiddleware` chama `Auth::check()` para validar a sessão e, nos métodos que alteram estado, chama `Csrf::validate()`. Token ausente ou inválido retorna HTTP 419.
- Rotas públicas de autenticação, como login, recuperação de senha, redefinição de senha e ativação de conta, não usam esse middleware. As demais rotas protegidas usam a combinação de sessão e CSRF definida em `api/routes/api.php`.
- Logout exige `POST` e token CSRF, inclusive nas URLs legadas de páginas. `GET`, `HEAD` e outros métodos nessas URLs retornam 405 sem encerrar a sessão.

## Política e limites

- `PasswordPolicy` exige de 12 a 128 caracteres, com número, letra maiúscula, letra minúscula e caractere especial.
- `AuthRateLimitException` identifica bloqueios temporários por excesso de tentativas e é convertida pelo controller em HTTP 429.

Observações

- Auth::check() retorna uma resposta 401 e encerra a execução se a sessão não estiver válida.
- A implementação atual usa sessions PHP nativas e deve ser adaptada se for migrada para tokens JWT ou outro método sem estado.
