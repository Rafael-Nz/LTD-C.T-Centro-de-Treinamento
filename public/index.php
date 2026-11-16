<?php

declare(strict_types=1);

/**
 * public/index.php
 *
 * Ponto de entrada unificado para ambientes onde o DocumentRoot do servidor web
 * (Apache / Nginx) aponta diretamente para esta pasta (public/).
 *
 * Benefício de segurança:
 * - O código da API (/api), Painel Admin (/admin), ferramentas (/tools),
 *   dependências (/vendor) e arquivos de configuração (.env, composer.json)
 *   ficam fisicamente FORA da raiz acessível via HTTP pelo servidor web.
 */

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/autoload.php';

$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$publicDir = str_replace('\\', '/', __DIR__);
$isDirectVhost = ($docRoot === $publicDir);

if (!defined('BASE_URL')) {
    define('BASE_URL', $isDirectVhost ? '/' : '/ctt/');
}
if (!defined('PUBLIC_URL')) {
    define('PUBLIC_URL', $isDirectVhost ? '/' : BASE_URL . 'public/');
}

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// 1. Roteamento para a API (/api/...)
if (preg_match('#(?:^|/)api(?:/|$)(.*)#', $requestUri, $matches)) {
    $_GET['url'] = ltrim($matches[1] ?? '', '/');
    require_once $projectRoot . '/api/bootstrap.php';
    exit;
}

// 2. Roteamento para o Painel Administrativo (/admin/...)
if (preg_match('#(?:^|/)admin(?:/|$)(.*)#', $requestUri, $matches)) {
    $_GET['url'] = ltrim($matches[1] ?? '', '/');
    require_once $projectRoot . '/admin/index.php';
    exit;
}

// 3. Roteamento para o Portal do Aluno e Páginas Públicas
$publicUrl = preg_replace('#^' . preg_quote(rtrim(BASE_URL, '/'), '#') . '/?#', '', $requestUri);
$_GET['url'] = ltrim($publicUrl, '/');
require_once __DIR__ . '/router.php';
