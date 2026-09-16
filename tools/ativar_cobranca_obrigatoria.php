<?php
require_once __DIR__ . '/financeiro_bootstrap.php';
try {
    \Core\Audit\Audit::begin('JOB', '/financeiro/contratos/cobranca-obrigatoria', []);
    $migration = new class extends \Core\Services\Service {
        public function run(): int {
            return $this->transaction(fn () => \Core\Database\Database::getConnection()->exec('UPDATE contrato SET geracao_automatica = 1 WHERE geracao_automatica = 0'));
        }
    };
    echo $migration->run() . " contrato(s) atualizado(s). Cobranças e pagamentos preservados.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
