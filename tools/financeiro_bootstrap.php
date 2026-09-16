<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
date_default_timezone_set('America/Sao_Paulo');
spl_autoload_register(function ($class) {
    $path = str_starts_with($class, 'Core\\')
        ? '/api/core/' . substr($class, 5)
        : '/api/src/' . $class;
    $parts = explode('\\', $path);
    if (!str_starts_with($class, 'Core\\')) $parts[0] = strtolower($parts[0]);
    $file = dirname(__DIR__) . str_replace('\\', '/', implode('\\', $parts)) . '.php';
    if (is_file($file)) require_once $file;
});
