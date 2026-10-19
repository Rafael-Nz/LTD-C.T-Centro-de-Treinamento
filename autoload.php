<?php

declare(strict_types=1);

/**
 * Autoloader global do sistema CTT
 */

// 1. Carrega dependências do Composer (Dotenv, PHPMailer, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Carrega variáveis de ambiente se ainda não carregadas
if (class_exists(\Dotenv\Dotenv::class) && file_exists(__DIR__ . '/.env')) {
    static $dotenvLoaded = false;
    if (!$dotenvLoaded) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
        $dotenvLoaded = true;
    }
}

// 3. Autoloader PSR-4 para classes de Core e módulos
spl_autoload_register(static function (string $class): void {
    $basePath = __DIR__ . '/api';
    $classPath = str_replace('\\', '/', $class);

    // Classes do Core (ex: Core\Auth\Auth -> api/core/Auth/Auth.php)
    if (str_starts_with($class, 'Core\\')) {
        $file = $basePath . '/core/' . substr($classPath, 5) . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    // Classes dos módulos (ex: Cargo\CargoRepository -> api/src/cargo/CargoRepository.php)
    $srcPath = $basePath . "/src/{$classPath}.php";
    if (is_file($srcPath)) {
        require_once $srcPath;
        return;
    }

    // Fallback: lowercase no primeiro segmento do módulo (ex: cargo/CargoRepository.php)
    $parts = explode('/', $classPath);
    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]);
        $fallbackSrc = $basePath . '/src/' . implode('/', $parts) . '.php';
        if (is_file($fallbackSrc)) {
            require_once $fallbackSrc;
            return;
        }
    }
});
