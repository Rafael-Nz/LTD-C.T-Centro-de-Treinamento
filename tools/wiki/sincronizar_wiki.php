<?php

declare(strict_types=1);

/**
 * tools/wiki/sincronizar_wiki.php
 * 
 * Script CLI em PHP puro para sincronizar a pasta docs/ para o repositório clonado da Wiki.
 * Multiplataforma (Windows, Linux, macOS) e sem dependência de PowerShell.
 * 
 * Uso:
 *   php tools/wiki/sincronizar_wiki.php /caminho/para/ctt.wiki
 *   php tools/wiki/sincronizar_wiki.php --destino="../ctt.wiki"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Este script deve ser executado exclusivamente via linha de comando (CLI).\n";
    exit(1);
}

$rootDir = dirname(__DIR__, 2);
$docsDir = $rootDir . DIRECTORY_SEPARATOR . 'docs';

// Obtém o destino dos argumentos
$destino = null;
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if (str_starts_with($arg, '--destino=')) {
        $destino = substr($arg, 10);
    } elseif ($destino === null && !str_starts_with($arg, '-')) {
        $destino = $arg;
    }
}

if (!$destino) {
    echo "Uso:\n";
    echo "  php tools/wiki/sincronizar_wiki.php <caminho_destino_wiki>\n";
    echo "Exemplo:\n";
    echo "  php tools/wiki/sincronizar_wiki.php ../ctt.wiki\n";
    exit(1);
}

$destinoReal = realpath($destino) ?: $destino;

if (!is_dir($docsDir)) {
    echo "[ERRO] Pasta docs não encontrada em: {$docsDir}\n";
    exit(1);
}

if (!is_dir($destinoReal)) {
    echo "[ERRO] Pasta de destino da Wiki não encontrada em: {$destinoReal}\n";
    echo "Certifique-se de clonar o repositório da Wiki previamente.\n";
    exit(1);
}

echo "\n======================================================\n";
echo "  Sincronização da Wiki (PHP Puro)\n";
echo "  Origem:  {$docsDir}\n";
echo "  Destino: {$destinoReal}\n";
echo "======================================================\n\n";

function copiarRecursivo(string $src, string $dst): int {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    $copiados = 0;

    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . DIRECTORY_SEPARATOR . $file;
        $dstPath = $dst . DIRECTORY_SEPARATOR . $file;

        if (is_dir($srcPath)) {
            $copiados += copiarRecursivo($srcPath, $dstPath);
        } else {
            if (copy($srcPath, $dstPath)) {
                $copiados++;
            }
        }
    }
    closedir($dir);
    return $copiados;
}

$total = copiarRecursivo($docsDir, $destinoReal);

echo "[SUCESSO] Sincronização concluída! {$total} arquivo(s) copiado(s) para {$destinoReal}.\n";
echo "Para publicar as alterações, acesse a pasta da wiki e execute:\n";
echo "  cd {$destinoReal}\n";
echo "  git add .\n";
echo "  git commit -m \"docs: sincronização da wiki\"\n";
echo "  git push\n\n";
