<?php
$baseDir = __DIR__ . '/';
$publicDir = $baseDir . 'public/';

define('BASE_URL', '/ctt/');
define('PUBLIC_URL', 'http://localhost/ctt/public/');

// Captura e processamento da URL
$url = $_GET['url'] ?? 'home';
$url = trim($url, '/');

// Ignorar requisições de arquivos estáticos (extensões comuns)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|pdf|map)$/i', $url)) {
    // Arquivo não encontrado (se chegou aqui, não existe em public/)
    http_response_code(404);
    exit;
}

$partes = explode('/', $url);
$pagina = $partes[0];
$caminhoView = $publicDir . $pagina . '.php';

if (file_exists($caminhoView)) {
    include $caminhoView;
} else {
    http_response_code(404);
    if (file_exists($publicDir . '404.php')) {
        include $publicDir . '404.php';
    } else {
        echo "<h1>Erro 404 - Página Não Encontrada</h1>";
        echo "<p>A URL <strong>/" . htmlspecialchars($url) . "</strong> não corresponde a nenhum recurso.</p>";
    }
}