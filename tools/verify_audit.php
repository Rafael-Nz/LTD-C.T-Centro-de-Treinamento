<?php
// CLI somente. Usa .env sem iniciar sessao/roteamento HTTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../api/core/Database/Database.php';
require_once __DIR__ . '/../api/core/Audit/AuditIntegrity.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
$options = getopt('', ['anchor:', 'checkpoint:']);
try {
    $anchor = null;
    if (isset($options['anchor'])) {
        $text = file_get_contents($options['anchor']);
        if ($text === false) throw new RuntimeException('Nao foi possivel ler o checkpoint.');
        $anchor = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($anchor)) throw new RuntimeException('Checkpoint invalido.');
    }
    $head = \Core\Audit\AuditIntegrity::verify(\Core\Database\Database::getConnection(), $anchor);
    if (isset($options['checkpoint'])) {
        // Nunca sobrescrever uma ancora anterior: cada checkpoint tem seu proprio arquivo.
        $file = fopen($options['checkpoint'], 'x');
        if (!$file) throw new RuntimeException('Destino ja existe ou nao permite gravacao.');
        $json = json_encode($head, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
        try {
            if (fwrite($file, $json) !== strlen($json) || !fflush($file)) throw new RuntimeException('Falha ao salvar checkpoint.');
        } finally { fclose($file); }
    }
    echo 'OK: cadeia integra; ' . $head['last_id'] . ' registros verificados.' . PHP_EOL;
    echo json_encode($head, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $e) {
    // Nao imprimir DSN, credenciais ou mensagens PDO.
    fwrite(STDERR, 'FALHA: ' . ($e instanceof PDOException ? 'Nao foi possivel consultar a auditoria.' : $e->getMessage()) . PHP_EOL);
    exit(1);
}
