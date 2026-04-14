<?php
// Caminho base da pasta admin
$baseDir = __DIR__ . '/';

// Captura da URL 
$url = $_GET['url'] ?? 'home';
$url = trim($url, '/'); // remove barras extras

$caminhoRelativo = $url . '.php';

$caminhoArquivo = $baseDir . $caminhoRelativo;

// Carrega a página, se existir
if (file_exists($caminhoArquivo)) {
    include $caminhoArquivo;
} else {
    // Página não encontrada — 404
    http_response_code(404);
    // Assumindo que o arquivo 404.php está no diretório base do admin
    include $baseDir . '404.php';
}