<?php
require_once dirname(__DIR__, 2) . '/financeiro/financeiro_bootstrap.php';
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->sqliteCreateFunction('CONCAT', fn (...$values) => implode('', $values));
$db->exec('CREATE TABLE usuario (id INTEGER PRIMARY KEY, nome TEXT, sobrenome TEXT);
CREATE TABLE cobranca (id INTEGER PRIMARY KEY, aluno_id INTEGER, descricao TEXT, competencia TEXT, data_vencimento TEXT, status TEXT, valor_final NUMERIC);
CREATE TABLE pagamento (id INTEGER PRIMARY KEY, cobranca_id INTEGER, valor_pago NUMERIC, estornado_em TEXT);
INSERT INTO usuario VALUES (1, "Ana", "Teste");
INSERT INTO pagamento VALUES (1, 1, 30, NULL), (2, 1, 20, "2026-09-14");');
for ($i = 1; $i <= 23; $i++) {
    $db->prepare('INSERT INTO cobranca VALUES (?, 1, ?, ?, ?, ?, 100)')->execute([$i, 'Mensalidade', '2026-09', '2026-09-10', $i % 2 ? 'aberta' : 'vencida']);
}
(new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
$repo = new \Financeiro\FinanceiroRepository();
$assert = function ($ok) { if (!$ok) throw new RuntimeException('Falha na listagem de cobranças.'); };
$first = $repo->cobrancasPaginadas(['length' => 10]);
$last = $repo->cobrancasPaginadas(['start' => 20, 'length' => 10]);
$assert($first['recordsTotal'] === 23 && count($first['data']) === 10 && count($last['data']) === 3);
$assert(!array_intersect(array_column($first['data'], 'id'), array_column($last['data'], 'id')));
$assert((float) $first['data'][0]['total_pago'] === 30.0 && $first['data'][0]['saldo'] === 70.0);
$assert($repo->cobrancasPaginadas(['status' => 'aberta', 'search' => ['value' => 'Ana Teste']])['recordsFiltered'] === 12);
$assert($repo->cobrancasPaginadas(['status' => 'aberta,vencida'])['recordsFiltered'] === 23);
$assert($repo->cobrancasPaginadas(['status' => 'cancelada'])['recordsFiltered'] === 0);
$assert($repo->cobrancasPaginadas(['search' => ['value' => "' OR 1=1 --"]])['recordsFiltered'] === 0);
echo "OK: paginação, filtros, busca, totais e exclusão dos pagamentos estornados.\n";
