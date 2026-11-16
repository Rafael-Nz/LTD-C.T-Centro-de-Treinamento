<?php
// admin/router.php
require_once dirname(__DIR__) . '/autoload.php';
\Core\Auth\Auth::setContext(\Core\Auth\Auth::GUARD_ADMIN);
// Garante cookie/sessão CSRF antes de qualquer saída (login, reset e ativação enviam o token).
\Core\Auth\Csrf::token();

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none';");
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    if (\Core\Auth\Auth::isSecure()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

class AdminRouter {
    private array $routes = [];
    private string $baseDir;
    
    public function __construct() {
        $this->baseDir = __DIR__ . '/views/';
    }
    
    /**
     * Adiciona uma rota amigável
     * Exemplo: $router->add('alunos/editar/{id}', 'aluno_form.php', ['acao' => 'editar']);
     */
    public function add(string $pattern, string $view, array $defaultParams = [], array $middlewares = []): void {
        // Converte padrão para regex
        $regex = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        $this->routes[$regex] = [
            'view' => $view,
            'defaultParams' => $defaultParams,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Processa a URL atual
     */
    public function dispatch(string $url): void {
        $url = trim($url, '/');

        if (!defined('BASE_URL')) {
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $isDirectVhost = ($docRoot === str_replace('\\', '/', dirname(__DIR__) . '/public'));
            define('BASE_URL', $isDirectVhost ? '/admin/' : '/ctt/admin/');
        }
        if (!defined('API_BASE_URL')) {
            $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $isDirectVhost = ($docRoot === str_replace('\\', '/', dirname(__DIR__) . '/public'));
            define('API_BASE_URL', $isDirectVhost ? '/api/' : '/ctt/api/');
        }

        foreach ($this->routes as $regex => $route) {
            if (preg_match($regex, $url, $matches)) {

                foreach ($route['middlewares'] as $middleware) {
                    if (is_callable($middleware)) {
                        $middleware(); 
                    }
                }

                if (empty($route['view'])) {
                    return; 
                }

                // Extrai parâmetros da URL
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) $params[$key] = $value;
                }
                
                // Adiciona parâmetros padrão
                $_GET = array_merge($_GET, $route['defaultParams'], $params);
                
                // Carrega a view
                $viewFile = $this->baseDir . $route['view'];
                if (file_exists($viewFile)) {
                    include $viewFile;
                    return;
                }
            }
        }
        
        // 404
        http_response_code(404);
        include $this->baseDir . '404.php';
    }
    
    /**
     * Gera URL a partir de uma rota nomeada
     */
    public function url(string $route, array $params = []): string {
        foreach ($this->routes as $regex => $config) {
            // Busca a rota que corresponde ao padrão
            $cleanPattern = str_replace(['#^', '$#'], '', $regex);
            $cleanPattern = str_replace(['(?P<', '>[^/]+)'], ['{', '}'], $cleanPattern);
            
            if ($cleanPattern === $route || strpos($cleanPattern, $route) !== false) {
                $url = $route;
                foreach ($params as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                return BASE_URL . $url;
            }
        }
        
        // Fallback para rota simples
        return BASE_URL . $route;
    }
    /**
     * Carrega rotas de um arquivo
     */
    public function loadRoutesFromFile(string $filePath): void {
        if (file_exists($filePath)) {
            $routeLoader = require $filePath;
            if (is_callable($routeLoader)) {
                $routeLoader($this);
            }
        }
    }
    
    /**
     * Carrega múltiplos arquivos de rotas
     */
    public function loadRoutesFromDirectory(string $directory): void {
        $files = glob($directory . '/*.php');
        foreach ($files as $file) {
            $this->loadRoutesFromFile($file);
        }
    }
}

// Inicializa o router
$router = new AdminRouter();

// Carrega as rotas do arquivo admin.php
$router->loadRoutesFromFile(__DIR__ . '/routes/admin.php');

// Ou carrega todas as rotas da pasta routes
// $router->loadRoutesFromDirectory(__DIR__ . '/routes');

// Processa a requisição
$url = $_GET['url'] ?? '';
$router->dispatch($url);
?>