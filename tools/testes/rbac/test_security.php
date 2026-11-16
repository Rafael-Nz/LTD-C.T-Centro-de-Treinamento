<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$tests = [
    'test_security_regressions.php',
    'test_security_http.php',
    'test_session_guard_isolation.php',
    'test_rbac_permissoes.php',
    'test_admin_router.php',
];
foreach ($tests as $test) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __DIR__ . '/' . $test], [STDIN, STDOUT, STDERR], $pipes);
    if (!is_resource($process) || proc_close($process) !== 0) {
        fwrite(STDERR, "Falha no teste {$test}.\n");
        exit(1);
    }
}
echo "OK: 5 grupos de seguranca/RBAC aprovados, sem banco ou sessoes em disco.\n";
