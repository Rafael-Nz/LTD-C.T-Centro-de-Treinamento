<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($projectRoot)->load();
date_default_timezone_set('America/Sao_Paulo');
spl_autoload_register(function ($class) use ($projectRoot) {
    $path = str_starts_with($class, 'Core\\')
        ? '/api/core/' . substr($class, 5)
        : '/api/src/' . $class;
    $parts = explode('\\', $path);
    if (!str_starts_with($class, 'Core\\')) $parts[0] = strtolower($parts[0]);
    $file = $projectRoot . str_replace('\\', '/', implode('\\', $parts)) . '.php';
    if (is_file($file)) require_once $file;
});
