<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$tests = [
    'calendario de cobrancas' => 'test_calendario_cobrancas.php',
    'paginacao de servicos e planos' => 'test_catalogo_paginacao.php',
    'paginacao de contratos' => 'test_contratos_paginacao.php',
    'paginacao de cobrancas' => 'test_cobrancas_paginacao.php',
    'acoes financeiras' => 'test_financeiro_acoes.php',
];

$failures = [];
foreach ($tests as $name => $file) {
    echo "\n== {$name} ==\n";
    $command = [PHP_BINARY, '-d', 'xdebug.mode=off', __DIR__ . DIRECTORY_SEPARATOR . $file];
    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes);
    if (!is_resource($process)) {
        $failures[] = "{$name}: nao foi possivel iniciar";
        continue;
    }

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        $failures[] = "{$name}: codigo {$exitCode}";
    }
}

if ($failures !== []) {
    fwrite(STDERR, "\nFALHA: " . implode('; ', $failures) . "\n");
    exit(1);
}

echo "\nOK: " . count($tests) . " grupos de testes financeiros rapidos aprovados.\n";
