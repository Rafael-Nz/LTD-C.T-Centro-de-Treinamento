<?php
/**
 * bootstrap.php - Inicializador do Sistema
 */

// 1. Configurações de exibição de erros (Em produção, mude para 0)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Fuso Horário (Alinhado com a classe Database e logs)
date_default_timezone_set('America/Sao_Paulo');

// 3. Autoload das Classes
spl_autoload_register(function ($class) {
    $basePath = __DIR__;

    // Converte namespace para caminho
    $classPath = str_replace('\\', '/', $class);

    // =========================
    // CORE (namespace Core\*)
    // =========================
    if (str_starts_with($class, 'Core\\')) {
        $file = $basePath . '/core/' . str_replace('Core/', '', $classPath) . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    // =========================
    // MODULES (v1)
    // =========================
    $modulesPath = $basePath . "/v1";

    if (is_dir($modulesPath)) {
        foreach (scandir($modulesPath) as $module) {

            if ($module === '.' || $module === '..') continue;

            $file = "{$modulesPath}/{$module}/" . basename($classPath) . ".php";

            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// 4. Configuração de Cabeçalhos HTTP para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Finaliza requisições de pre-flight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =====================================================
// ROTEAMENTO
// =====================================================

$uri = $_GET['url'] ?? $_SERVER['REQUEST_URI'] ?? '';
$uri = str_replace('/ctt/api/', '', $uri);
$uri = trim($uri, '/');

$partes = explode('/', $uri);

// Remove versão
if (($partes[0] ?? null) === 'v1') {
    array_shift($partes);
}

$modulo = $partes[0] ?? null;

if (!$modulo) {
    echo json_encode(['erro' => 'Rota não especificada']);
    exit;
}

// Remove módulo da lista
array_shift($partes);

// Controller correto com namespace
$controllerName = ucfirst($modulo) . '\\' . ucfirst($modulo) . 'Controller';

if (!class_exists($controllerName)) {
    http_response_code(404);
    echo json_encode(['erro' => 'Controller não encontrado']);
    exit;
}

$controller = new $controllerName();

// Método HTTP
$httpMethod = $_SERVER['REQUEST_METHOD'];

switch ($httpMethod) {
    case 'GET':
        $method = 'index';
        break;
    case 'POST':
        $method = 'store';
        break;
    case 'PUT':
        $method = 'update';
        break;
    case 'DELETE':
        $method = 'destroy';
        break;
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
        exit;
}

// Executa
$controller->$method($partes);