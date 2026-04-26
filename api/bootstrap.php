<?php
/**
 * api/bootstrap.php - Inicializador da API
 * 
 * Entry point único para todas as requisições
 */

// 1. CONFIGURAÇÕES INICIAIS

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Fortaleza');

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
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Responde a pre-flight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 4. CARREGA ROTAS E EXECUTA DISPATCHER

require_once __DIR__ . '/routes/api.php';

$router = new \Core\Router();
$router->dispatch();
