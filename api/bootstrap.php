<?php

/**
 * api/bootstrap.php - Inicializador da API
 *
 * Entry point único para todas as requisições
 */

// 1. CONFIGURAÇÕES INICIAIS

error_reporting(E_ALL);

date_default_timezone_set('America/Fortaleza');

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$isDevelopment = ($_ENV['APP_ENV'] ?? 'production') === 'development';
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('display_startup_errors', $isDevelopment ? '1' : '0');

// 2. AUTOLOAD (PSR-4)

spl_autoload_register(function ($class) {
    $basePath = __DIR__;

    // Converte namespace para caminho
    $classPath = str_replace('\\', '/', $class);

    // Core classes (Core\ClassName)
    if (str_starts_with($class, 'Core\\')) {
        $file = $basePath . '/core/' . str_replace('Core/', '', $classPath) . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    // Source classes (Modulo\ClassName)
    $srcPath = $basePath . "/src/{$classPath}.php";
    if (is_file($srcPath)) {
        require_once $srcPath;
        return;
    }
});

// 3. HEADERS HTTP (CORS e Content-Type)

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$allowedOrigin = trim($_ENV['APP_ALLOWED_ORIGIN'] ?? '');
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($allowedOrigin !== '' && hash_equals($allowedOrigin, $requestOrigin)) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}

\Core\Auth\Csrf::token();

// Responde a pre-flight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 4. CARREGA ROTAS E EXECUTA DISPATCHER

require_once __DIR__ . '/routes/api.php';

$router = new \Core\Http\Router();
$router->dispatch();
