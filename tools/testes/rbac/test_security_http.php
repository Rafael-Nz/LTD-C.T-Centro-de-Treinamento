<?php

declare(strict_types=1);

// Processos separados exercitam os handlers reais, inclusive seus exit/headers.
if (($argv[1] ?? '') === 'child') {
    require_once __DIR__ . '/security_fixture.php';
    securityTestDatabase();
    \Core\Auth\Auth::login(['id' => 2], 'admin');
    \Core\Auth\Auth::login(['id' => 10], 'aluno');
    $mode = $argv[2];
    securityNextRequest($mode === 'turma-aluno' ? 'aluno' : null, $argv[3] ?? 'GET');
    $_GET['url'] = 'logout';
    $_SERVER['REQUEST_URI'] = $mode === 'admin' ? '/ctt/admin/logout' : '/ctt/logout';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = ($argv[4] ?? '') === 'valid' ? \Core\Auth\Csrf::token() : 'invalido';
    register_shutdown_function(static function (): void {
        $body = ob_get_clean();
        echo json_encode([
            'status' => http_response_code(), 'body' => $body,
            'admin' => isset($_SESSION['auth_admin']), 'aluno' => isset($_SESSION['auth_aluno']),
        ]);
    });
    if ($mode === 'admin') {
        require dirname(__DIR__, 3) . '/admin/router.php';
    } elseif ($mode === 'aluno') {
        require dirname(__DIR__, 3) . '/public/router.php';
    } elseif ($mode === 'turma-aluno') {
        \Core\Auth\Auth::logout('admin');
        require dirname(__DIR__, 3) . '/api/routes/api.php';
        $routes = (new ReflectionProperty(\Core\Http\Router::class, 'routes'))->getValue();
        foreach ($routes['GET'] as $route) {
            if ($route['path'] !== '/turmas/{id}') continue;
            foreach ($route['middlewares'] as $middleware) {
                $instance = is_string($middleware) ? new $middleware() : $middleware;
                if ($instance->handle() === false) exit;
            }
            echo 'ROSTER_LIBERADO';
            exit;
        }
    }
    exit;
}

function securityChild(string $area, string $method, string $token): array
{
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, 'child', $area, $method, $token],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Nao foi possivel iniciar o teste.');
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exit = proc_close($process);
    $result = json_decode($out, true);
    if ($exit !== 0 || $err !== '' || !is_array($result)) throw new RuntimeException($err . $out);
    return $result;
}

$checks = 0;
foreach (['admin', 'aluno'] as $area) {
    foreach (['GET', 'HEAD', 'PUT'] as $method) {
        $r = securityChild($area, $method, 'valid');
        if ($r['status'] !== 405 || !$r['admin'] || !$r['aluno']) throw new RuntimeException('Logout alterou sessao por ' . $method);
        $checks++;
    }
    $r = securityChild($area, 'POST', 'invalid');
    if ($r['status'] !== 419 || !$r['admin'] || !$r['aluno']) throw new RuntimeException('Logout sem CSRF foi aceito.');
    $checks++;
    $r = securityChild($area, 'POST', 'valid');
    $other = $area === 'admin' ? 'aluno' : 'admin';
    if ($r['status'] !== 302 || $r[$area] || !$r[$other]) throw new RuntimeException('Logout legitimo ou independencia dos guards falhou.');
    $checks++;
}
$r = securityChild('turma-aluno', 'GET', 'valid');
if ($r['status'] !== 401 || str_contains($r['body'], 'ROSTER_LIBERADO')) throw new RuntimeException('Aluno alcancou o detalhe administrativo.');
$checks++;
echo "OK: {$checks} verificacoes de logout real e bloqueio de roster.\n";
