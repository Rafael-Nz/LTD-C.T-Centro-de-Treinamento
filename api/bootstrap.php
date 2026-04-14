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

    $coreFile = $basePath . "/core/{$class}.php";
    if (is_file($coreFile)) {
        require_once $coreFile;
        return;
    }

    $modulesPath = $basePath . "/v1";

    if (is_dir($modulesPath)) {
        foreach (scandir($modulesPath) as $module) {

            if ($module === '.' || $module === '..') {
                continue;
            }

            $file = "{$modulesPath}/{$module}/{$class}.php";

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