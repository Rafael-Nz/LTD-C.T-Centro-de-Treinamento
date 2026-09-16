<?php
require_once __DIR__ . '/financeiro_bootstrap.php';
try {
    \Core\Audit\Audit::begin('JOB', '/financeiro/cobrancas/gerar-automaticas', []);
    $resultado = (new \Financeiro\FinanceiroService())->gerarAutomaticas();
    echo date('c') . ' ' . json_encode($resultado) . PHP_EOL;
} catch (Throwable $e) {
    http_response_code(500);
    fwrite(STDERR, 'Falha na geracao automatica: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
