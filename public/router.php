<?php

if (!defined('BASE_URL')) define('BASE_URL', '/ctt/');
if (!defined('PUBLIC_URL')) define('PUBLIC_URL', BASE_URL . 'public/');

require_once dirname(__DIR__) . '/api/core/Auth/Auth.php';
require_once dirname(__DIR__) . '/api/core/Auth/Csrf.php';

use Core\Auth\Auth;
use Core\Auth\Csrf;

final class PublicRouter
{
    private array $routes = [];
    private string $viewsDir;

    public function __construct()
    {
        $this->viewsDir = __DIR__ . '/views/';
        Auth::setContext(Auth::GUARD_ALUNO);
        Auth::start();
    }

    public function add(string $pattern, string $view, array $middlewares = []): void
    {
        $regex = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', trim($pattern, '/'));
        $this->routes['#^' . $regex . '$#'] = compact('view', 'middlewares');
    }

    public function dispatch(string $url): void
    {
        $url = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        foreach ($this->routes as $regex => $route) {
            if (!preg_match($regex, $url, $matches)) continue;
            foreach ($route['middlewares'] as $middleware) $middleware();
            $_GET = array_merge($_GET, array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY));
            $viewFile = $this->viewsDir . $route['view'];
            if (is_file($viewFile)) {
                require $viewFile;
                return;
            }
        }
        http_response_code(404);
        $message = 'A página solicitada não foi encontrada.';
        require $this->viewsDir . '404.php';
    }
}

$studentAuth = static function (): void {
    Auth::setContext(Auth::GUARD_ALUNO);
    $user = Auth::guard(Auth::GUARD_ALUNO)->user();
    if (!$user || ($user['tipo'] ?? null) !== 'aluno') {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
};

$loginGuest = static function (): void {
    Auth::setContext(Auth::GUARD_ALUNO);
    $user = Auth::guard(Auth::GUARD_ALUNO)->user();
    if ($user && ($user['tipo'] ?? null) === 'aluno') {
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }
};

$router = new PublicRouter();
$router->add('', 'home.php');
$router->add('login', 'login.php', [$loginGuest]);
$router->add('ativar-conta/{token}', 'ativar-conta.php');
$router->add('dashboard', 'dashboard.php', [$studentAuth]);
$router->add('dados-pessoais', 'dados-pessoais.php', [$studentAuth]);
$router->add('matricula', 'matricula.php', [$studentAuth]);
$router->add('avaliacoes', 'avaliacoes.php', [$studentAuth]);
$router->add('horarios', 'horarios.php', [$studentAuth]);
$router->add('financeiro', 'financeiro.php', [$studentAuth]);
$router->add('logout', '', [static function (): void {
    Auth::logout(Auth::GUARD_ALUNO);
    header('Location: ' . BASE_URL . 'login');
    exit;
}]);

// Garante que as páginas públicas que fazem requisições POST tenham um token CSRF.
Csrf::token();
$router->dispatch($_GET['url'] ?? '');
