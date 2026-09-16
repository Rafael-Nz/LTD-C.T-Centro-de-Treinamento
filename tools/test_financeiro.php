<?php
// Somente servidor isolado de testes na porta 33317, com datadir ctt-financeiro-*.
require_once __DIR__ . '/financeiro_bootstrap.php';
use Financeiro\FinanceiroService as S;
use Financeiro\FinanceiroRepository as R;
use Financeiro\FinanceiroValidation as V;
use Financeiro\DTO\ContratoDTO;
use Financeiro\DTO\PagamentoDTO;

$db = new PDO('mysql:host=127.0.0.1;port=33317;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
if (!str_contains(str_replace('\\', '/', $db->query('SELECT @@datadir')->fetchColumn()), '/ctt-financeiro-')) throw new RuntimeException('Servidor nao isolado.');
$db->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
function sqlFile(PDO $db, string $sql): void {
    $delimiter = ';'; $buffer = '';
    foreach (preg_split('/\R/', $sql) as $line) {
        if (preg_match('/^DELIMITER\s+(\S+)/i', trim($line), $m)) { $delimiter = $m[1]; continue; }
        if (str_starts_with(trim($line), '--') || trim($line) === '') continue;
        $buffer .= $line . "\n";
        if (str_ends_with(rtrim($buffer), $delimiter)) {
            $stmt = $db->query(substr(rtrim($buffer), 0, -strlen($delimiter))); $stmt->closeCursor(); $buffer = '';
        }
    }
}
if (in_array($argv[1] ?? '', ['--setup', '--migration'], true)) {
    sqlFile($db, str_replace('db_centro_treinamento', 'ctt_financeiro_test', file_get_contents(__DIR__ . '/../docs/sql/setup/banco.sql')));
    $seed = file_get_contents(__DIR__ . '/../docs/sql/testes/sistema_seed.sql');
    // O marcador INSERT SELECT legado exige id explicito nesta versao do MariaDB.
    $seed = preg_replace('/INSERT INTO audit_logs.*?;/s', '', $seed);
    sqlFile($db, str_replace('db_centro_treinamento', 'ctt_financeiro_test', $seed));
    if (($argv[1] ?? '') === '--migration') {
        foreach (['contrato', 'cobranca', 'pagamento'] as $table) foreach (['insert', 'update', 'delete'] as $op) $db->exec("DROP TRIGGER audit_{$table}_{$op}");
        $db->exec('ALTER TABLE contrato DROP COLUMN periodicidade, DROP COLUMN geracao_automatica');
        $db->exec('ALTER TABLE cobranca DROP COLUMN cancelada_em, DROP COLUMN cancelada_por, DROP COLUMN motivo_cancelamento');
        $db->exec('ALTER TABLE pagamento DROP INDEX uq_pagamento_operacao, DROP INDEX uq_pagamento_transacao, DROP COLUMN transacao_unica, DROP COLUMN chave_idempotencia, DROP COLUMN requisicao_hash, DROP COLUMN estornado_em, DROP COLUMN estornado_por, DROP COLUMN motivo_estorno');
        sqlFile($db, file_get_contents(__DIR__ . '/../docs/sql/migrations/financeiro_seguranca.sql'));
        check((int) $db->query('SELECT COUNT(*) FROM contrato WHERE geracao_automatica <> 0')->fetchColumn() === 0, 'Migracao habilitou contratos antigos');
    }
    echo "Banco isolado preparado.\n";
    exit;
}
$db->exec('USE ctt_financeiro_test');
$db->exec("SET time_zone = '-03:00'");
(new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
$service = new S(); $repo = new R();
function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function reject(callable $fn, string $message): void { try { $fn(); } catch (InvalidArgumentException | RuntimeException $e) { if ($e instanceof PDOException) throw $e; return; } throw new RuntimeException($message); }
function pay(string $key, string $value): PagamentoDTO { return PagamentoDTO::fromArray(['valor_pago' => $value, 'forma_pagamento' => 'pix', 'chave_idempotencia' => $key]); }
if (($argv[1] ?? '') === '--worker') {
    try { $id = $service->registrarPagamento((int) $argv[2], pay($argv[3], $argv[4])); echo 'PAGO:' . $id; }
    catch (InvalidArgumentException $e) { echo 'RECUSADO'; }
    exit;
}
\Core\Audit\Audit::begin('JOB', '/financeiro/cobrancas/gerar-automaticas', []);

reject(fn () => V::centavos('10.001'), 'Aceitou fracao de centavo');
reject(fn () => V::centavos(-1), 'Aceitou negativo');
reject(fn () => V::data('2026-02-30'), 'Aceitou data inexistente');
reject(fn () => V::data('2026-13-01'), 'Aceitou mes inexistente');
check(V::centavos('0.10') + V::centavos('0.20') === 30, 'Imprecisao monetaria');
reject(fn () => ContratoDTO::fromArray(['dia_vencimento' => 1.5]), 'Aceitou dia fracionario');
$base = ['data_inicio' => '2026-01-15', 'dia_vencimento' => 10, 'data_fim' => null, 'periodicidade' => 'trimestral'];
check(S::vencimentos($base, new DateTimeImmutable('2026-09-30')) === ['2026-02-10', '2026-05-10', '2026-08-10'], 'Cadencia trimestral incorreta');
check(S::vencimentos(array_replace($base, ['periodicidade' => 'avulso']), new DateTimeImmutable('2026-09-30')) === ['2026-02-10'], 'Avulso duplicado');
check(S::vencimentos(array_replace($base, ['data_fim' => '2026-05-01']), new DateTimeImmutable('2026-09-30')) === ['2026-02-10'], 'Cobranca apos fim');

$aluno = (int) $db->query('SELECT usuario_id FROM aluno LIMIT 1')->fetchColumn();
$contrato = $service->criarContrato(ContratoDTO::fromArray(['aluno_id' => $aluno, 'data_inicio' => '2026-01-01', 'data_fim' => '2026-12-31', 'dia_vencimento' => 10, 'valor_contratado' => '100.00', 'periodicidade' => 'mensal', 'geracao_automatica' => false]));
$invoice = fn ($month) => $service->gerarCobranca($contrato, ['competencia' => '2026-' . $month, 'data_vencimento' => '2026-' . $month . '-10']);
$id = $invoice('01');
check($invoice('01') === $id, 'Cobranca duplicada');
$key = bin2hex(random_bytes(16));
$payment = $service->registrarPagamento($id, pay($key, '60.00'));
check($service->registrarPagamento($id, pay($key, '60.00')) === $payment, 'Reenvio duplicou pagamento');
reject(fn () => $service->registrarPagamento($id, pay($key, '61.00')), 'Reutilizacao de chave com outro valor aceita');
reject(fn () => $service->registrarPagamento($id, pay(bin2hex(random_bytes(16)), '40.01')), 'Aceitou centavo excedente');
reject(fn () => $service->cancelarCobranca($id, 'Cancelamento de teste', null), 'Cancelou recebimento sem estorno');
$second = $service->registrarPagamento($id, pay(bin2hex(random_bytes(16)), '40.00'));
check($repo->cobranca($id)['status'] === 'paga', 'Nao quitou');
reject(fn () => $service->estornarPagamento($id, $payment, '', null), 'Estornou sem justificativa');
$service->estornarPagamento($id, $payment, 'Recebimento informado incorretamente', null);
$service->estornarPagamento($id, $payment, 'Repeticao do pedido de estorno', null);
check(V::centavos($repo->totalPago($id)) === 4000 && $repo->cobranca($id)['status'] !== 'paga', 'Estorno nao reabriu saldo');
check($service->registrarPagamento($id, pay($key, '60.00')) === $payment && V::centavos($repo->totalPago($id)) === 4000, 'Retry ressuscitou pagamento estornado');
$service->estornarPagamento($id, $second, 'Recebimento informado incorretamente', null);
$service->cancelarCobranca($id, 'Contrato cancelado pelo aluno', null);
check($repo->cobranca($id)['status'] === 'cancelada' && count($repo->pagamentos($id)) === 2, 'Historico perdido');
reject(fn () => $service->registrarPagamento($id, pay(bin2hex(random_bytes(16)), '1.00')), 'Recebeu em cancelada');
check($invoice('01') === $id, 'Recriou cobranca cancelada');
check((int) $db->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'payment_reversed'")->fetchColumn() >= 2, 'Estorno sem auditoria');
$stmt = $db->prepare("SELECT JSON_UNQUOTE(JSON_EXTRACT(new_data, '$.motivo_estorno')) FROM audit_logs WHERE action = 'payment_reversed' AND entity_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$payment]);
check($stmt->fetchColumn() === 'Recebimento informado incorretamente', 'Justificativa ausente no log');

$failInvoice = $invoice('02');
$db->exec("CREATE TRIGGER test_fail_invoice BEFORE UPDATE ON cobranca FOR EACH ROW BEGIN IF NEW.id = $failInvoice AND NEW.status = 'paga' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falha simulada'; END IF; END");
$logCount = (int) $db->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
try { $service->registrarPagamento($failInvoice, pay(bin2hex(random_bytes(16)), '100.00')); throw new RuntimeException('Falha nao ocorreu'); }
catch (PDOException $e) { check(V::centavos($repo->totalPago($failInvoice)) === 0, 'Pagamento sobreviveu ao rollback'); check((int) $db->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn() === $logCount, 'Auditoria persistiu alteracao revertida'); }
$db->exec('DROP TRIGGER test_fail_invoice');

$raceInvoice = $invoice('03');
$processes = [];
foreach ([1, 2] as $i) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--worker', (string) $raceInvoice, bin2hex(random_bytes(16)), '100.00'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $processes[] = [$process, $pipes];
}
$results = [];
foreach ($processes as [$process, $pipes]) { $results[] = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); check(proc_close($process) === 0, $err); }
check(count(array_filter($results, fn ($r) => str_starts_with($r, 'PAGO:'))) === 1 && V::centavos($repo->totalPago($raceInvoice)) === 10000, 'Concorrencia excedeu saldo');

$retryInvoice = $invoice('04');
$sameKey = bin2hex(random_bytes(16));
$processes = [];
foreach ([1, 2] as $i) {
    $process = proc_open([PHP_BINARY, '-d', 'xdebug.mode=off', __FILE__, '--worker', (string) $retryInvoice, $sameKey, '100.00'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $processes[] = [$process, $pipes];
}
$results = [];
foreach ($processes as [$process, $pipes]) { $results[] = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); check(proc_close($process) === 0, $err); }
check($results[0] === $results[1] && str_starts_with($results[0], 'PAGO:') && count($repo->pagamentos($retryInvoice)) === 1, 'Retries simultaneos duplicados');

$service->configurarGeracao($contrato, true);
$first = $service->gerarAutomaticas('2026-09-14');
$repeat = $service->gerarAutomaticas('2026-09-14');
check($first['geradas'] === 5 && $repeat['geradas'] === 0, 'Geracao nao idempotente');
check($repo->cobranca($id)['status'] === 'cancelada', 'Rotina reativou cancelada');
echo "OK: valores, datas, periodicidade, pagamento parcial, idempotencia, estorno, cancelamento, auditoria, rollback e concorrencia real.\n";
