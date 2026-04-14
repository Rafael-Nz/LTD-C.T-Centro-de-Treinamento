
<?php
$baseDir = __DIR__ . '/';

// Caminho ABSOLUTO da pasta public
// Ex: C:/xampp/htdocs/ctt/public/
$publicDir = $baseDir . 'public/';

// URL base do sistema (IMPORTANTE)
// Altere /ctt/ caso sua pasta tenha outro nome
define('BASE_URL', '/ctt/');

// URL ABSOLUTA da pasta public (para imagens, CSS, JS)
define('PUBLIC_URL', 'http://localhost/ctt/public/');


// =============================================================
//  CAPTURA E PROCESSAMENTO DA URL
// =============================================================

$url = $_GET['url'] ?? 'home';
$url = trim($url, '/');  

// Divide por barras (ex: /treinos/editar/10)
$partes = explode('/', $url);

// A primeira parte é a página
$pagina = $partes[0];

// Caminho para o arquivo dentro de /public
$caminhoView = $publicDir . $pagina . '.php';


// =============================================================
//  ROTEAMENTO SIMPLES: carrega a view se existir
// =============================================================
if (file_exists($caminhoView)) {
    include $caminhoView;
} else {
    http_response_code(404);
    // Se existir uma view 404 personalizada
    if (file_exists($publicDir . '404.php')) {
        include $publicDir . '404.php';
    } else {
        echo "<h1>Erro 404 - Página Não Encontrada</h1>";
        echo "<p>A URL <strong>/" . htmlspecialchars($url) . "</strong> não corresponde a nenhum recurso.</p>";
    }
}

