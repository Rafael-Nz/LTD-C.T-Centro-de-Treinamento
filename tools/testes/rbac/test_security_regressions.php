<?php

declare(strict_types=1);

require_once __DIR__ . '/security_fixture.php';

use Core\Auth\Auth;
use Core\Auth\Csrf;
use Core\Http\Router;

$db = securityTestDatabase();
$checks = 0;
function secureAssert(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) throw new RuntimeException('FALHA: ' . $message);
    $checks++;
}
function loginBoth(): void
{
    Auth::login(['id' => 2], 'admin');
    Auth::login(['id' => 10], 'aluno');
}
require dirname(__DIR__, 3) . '/api/routes/api.php';
function routeMiddleware(string $method, string $path): array
{
    $routes = (new ReflectionProperty(Router::class, 'routes'))->getValue();
    foreach ($routes[$method] as $route) if ($route['path'] === $path) return $route['middlewares'];
    throw new RuntimeException('Rota ausente: ' . $path);
}
function sharedAllowed(string $path = '/usuarios/{id}', string $method = 'PUT'): bool
{
    $middleware = routeMiddleware($method, $path)[0];
    ob_start();
    $allowed = $middleware->handle();
    ob_end_clean();
    return $allowed;
}

// O controlador real e seus repositorios executam contra a fixture isolada.
class ProbeUsuarioController extends \Usuario\UsuarioController
{
    public int $status = 0;
    public array $payload = [];
    protected function body(): array { return $this->payload; }
    protected function json($data = null, int $status = 200): void { $this->status = $status; }
    protected function error(string $message, int $status = 400): void { $this->status = $status; }
}

loginBoth();
foreach (['admin', null, 'invalida'] as $area) {
    securityNextRequest($area, 'PUT');
    $_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::token();
    secureAssert(!sharedAllowed(), 'Aluno nao pode emprestar autorizacao ao funcionario sem usuarios.gerenciar.');
    secureAssert($db->query('SELECT email FROM usuario WHERE id = 1')->fetchColumn() === 'teste1@example.invalid', 'Email administrativo permanece intacto.');
}
securityNextRequest('aluno', 'PUT');
$_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::token();
secureAssert(sharedAllowed() && Auth::id() === 10 && Auth::user('tipo') === 'aluno', 'Guard aluno e principal do handler coincidem.');
$controller = new ProbeUsuarioController();
$controller->payload = ['email' => 'atacante@example.invalid'];
$controller->update(1);
secureAssert($controller->status === 403, 'Escrita em outro usuario bloqueada mesmo com os dois guards.');
secureAssert($db->query('SELECT email FROM usuario WHERE id = 1')->fetchColumn() === 'teste1@example.invalid', 'Nao ha caminho para alterar destino de recuperacao admin.');
$controller->payload = ['email' => 'proprio@example.invalid'];
$controller->update(10);
secureAssert($controller->status === 200 && $db->query('SELECT email FROM usuario WHERE id = 10')->fetchColumn() === 'proprio@example.invalid', 'Aluno continua atualizando o proprio cadastro.');

foreach (['/financeiro/contratos', '/financeiro/cobrancas', '/financeiro/metricas', '/alunos/{id}', '/avaliacoes/{id}', '/anamnese/respostas/{aluno_id}'] as $path) {
    securityNextRequest('admin');
    $allowed = sharedAllowed($path, 'GET');
    secureAssert($allowed === ($path === '/avaliacoes/{id}'), 'Acesso administrativo segue as permissoes reais do funcionario: ' . $path);
    securityNextRequest('aluno');
    secureAssert(sharedAllowed($path, 'GET') && Auth::id() === 10, 'Leitura de autosservico permanece em contexto aluno.');
}
// Permissao concedida ao funcionario deve continuar funcionando.
$db->exec("INSERT INTO cargo_permissao SELECT 2, id FROM permissao WHERE slug = 'usuarios.gerenciar'");
securityNextRequest('admin', 'PUT');
$_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::token();
secureAssert(sharedAllowed() && Auth::id() === 2, 'Funcionario autorizado usa sua propria identidade.');
$controller->payload = ['nome' => 'Nome autorizado'];
$controller->update(99);
secureAssert($controller->status === 200 && $db->query('SELECT nome FROM usuario WHERE id = 99')->fetchColumn() === 'Nome autorizado', 'Atualizacao administrativa legitima preservada.');
$db->exec("DELETE FROM cargo_permissao WHERE permissao_id IN (SELECT id FROM permissao WHERE slug = 'usuarios.gerenciar')");
securityNextRequest('admin');
secureAssert(!Auth::can('usuarios.gerenciar'), 'Remocao de permissao afeta a proxima requisicao.');

// Roster: detalhe e administrativo, listagem/calendario continua compartilhada.
securityNextRequest('aluno');
secureAssert(sharedAllowed('/turmas', 'GET'), 'Aluno pode listar horarios/turmas.');
$detail = routeMiddleware('GET', '/turmas/{id}');
secureAssert(count($detail) === 2 && $detail[1] instanceof \Core\Auth\PermissionMiddleware, 'Detalhe com contatos exige middleware de permissao administrativa.');
securityNextRequest('admin');
secureAssert($detail[0]->handle() && $detail[1]->handle() && Auth::user('tipo') === 'funcionario', 'Funcionario com turmas.visualizar mantem acesso ao detalhe.');

// Revalidacao por requisicao: sem confiar no estado legado ou em copias de privilegios.
$db->exec('UPDATE usuario SET ativo = 0 WHERE id = 2');
securityNextRequest('admin');
secureAssert(Auth::admin()->id() === null && Auth::admin()->user() === null && !Auth::can('turmas.visualizar'), 'Desativacao invalida ID, usuario e permissoes.');
secureAssert(Auth::aluno()->id() === 10, 'Revogar funcionario preserva outro guard valido.');
$db->exec('UPDATE usuario SET ativo = 1 WHERE id = 2');
securityNextRequest('admin');
secureAssert(!Auth::admin()->isAuthenticated(), 'Reativar cadastro nao ressuscita sessao revogada.');
Auth::login(['id' => 2], 'admin');
$db->exec('UPDATE funcionario SET cargo_id = 3 WHERE usuario_id = 2');
securityNextRequest('admin');
secureAssert(Auth::cargoId() === 3 && Auth::permissions() === [], 'Mudanca de cargo recarrega autorizacao e metadados.');
$db->exec('UPDATE cargo SET ativo = 0 WHERE id = 3');
securityNextRequest('admin');
secureAssert(!Auth::admin()->isAuthenticated(), 'Cargo desativado revoga o guard administrativo.');
$db->exec('UPDATE cargo SET ativo = 1 WHERE id = 3; UPDATE funcionario SET cargo_id = 2 WHERE usuario_id = 2');
Auth::login(['id' => 2], 'admin');
$db->exec("UPDATE funcionario SET senha = 'hash-alterado' WHERE usuario_id = 2");
securityNextRequest('admin');
secureAssert(!Auth::admin()->isAuthenticated() && Auth::aluno()->isAuthenticated(), 'Reset de senha funcionario revoga apenas seu perfil.');
Auth::login(['id' => 1], 'admin');
$db->exec("UPDATE usuario SET senha = 'novo-hash-admin' WHERE id = 1");
securityNextRequest('admin');
secureAssert(!Auth::admin()->isAuthenticated(), 'Reset administrativo invalida sessao anterior.');
Auth::login(['id' => 6], 'admin');
Auth::login(['id' => 6], 'aluno');
$db->exec("UPDATE aluno SET senha = 'novo-hash-aluno' WHERE usuario_id = 6");
securityNextRequest('aluno');
secureAssert(!Auth::aluno()->isAuthenticated() && Auth::admin()->id() === 6, 'Senhas de perfis da mesma pessoa sao independentes.');
$db->exec('DELETE FROM funcionario WHERE usuario_id = 6');
securityNextRequest('admin');
secureAssert(!Auth::admin()->isAuthenticated(), 'Remover perfil invalida sessao.');

$_SESSION = ['user_id' => 1, 'user_tipo' => 'admin', 'user_permissions' => ['*'], 'user' => ['id' => 1, 'tipo' => 'admin']];
securityNextRequest('admin');
secureAssert(Auth::id() === null && !Auth::isAdmin() && Auth::permissions() === [] && Auth::cargoId() === null, 'Espelho legado nao autentica nem ressuscita privilegios.');
$_SESSION['auth_admin'] = ['user_id' => 1, 'user_tipo' => 'admin'];
secureAssert(!Auth::admin()->isAuthenticated(), 'Sessao anterior sem fingerprint exige novo login.');
Auth::login(['id' => 10], 'aluno');
secureAssert(Auth::aluno()->isAuthenticated(), 'Primeiro acesso com conta_ativada=0 permanece permitido.');
$failed = false;
try { Auth::login(['id' => 10], 'aluno', 'hash-obsoleto'); } catch (RuntimeException $e) { $failed = true; }
secureAssert($failed, 'Hash alterado entre verificacao de senha e criacao da sessao e rejeitado.');

echo "OK: {$checks} verificacoes isoladas de guards, propriedade, roster e revogacao.\n";
