<?php
require_once __DIR__ . '/financeiro_bootstrap.php';
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->sqliteCreateFunction('CONCAT', fn (...$values) => implode('', $values));
$db->exec('CREATE TABLE usuario (id INTEGER PRIMARY KEY, nome TEXT, sobrenome TEXT);
CREATE TABLE plano (id INTEGER PRIMARY KEY, nome TEXT);
CREATE TABLE contrato (id INTEGER PRIMARY KEY, aluno_id INTEGER, plano_id INTEGER, data_inicio TEXT, status TEXT, geracao_automatica INTEGER);
INSERT INTO usuario VALUES (1, "Ana", "Teste"), (2, "Bruno", "Teste");
INSERT INTO plano VALUES (1, "Mensal"), (2, "Trimestral");');
for ($i = 1; $i <= 23; $i++) {
    $db->prepare('INSERT INTO contrato VALUES (?, ?, ?, ?, ?, ?)')->execute([$i, $i % 2 + 1, $i % 2 + 1, '2026-09-01', $i % 2 ? 'ativo' : 'pausado', $i % 2]);
}
(new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
$repo = new \Financeiro\FinanceiroRepository();
$assert = function ($ok) { if (!$ok) throw new RuntimeException('Falha na consulta paginada.'); };
$first = $repo->contratosPaginados(['start' => 0, 'length' => 10]);
$last = $repo->contratosPaginados(['start' => 20, 'length' => 10]);
$assert($first['recordsTotal'] === 23 && count($first['data']) === 10 && count($last['data']) === 3);
$assert(count(array_intersect(array_column($first['data'], 'id'), array_column($last['data'], 'id'))) === 0);
$filtered = $repo->contratosPaginados(['status' => 'ativo', 'plano_id' => '2', 'geracao_automatica' => '1', 'search' => ['value' => 'Bruno Teste']]);
$assert($filtered['recordsTotal'] === 23 && $filtered['recordsFiltered'] === 12 && count($filtered['data']) === 10);
$assert($repo->contratosPaginados(['status' => 'cancelado'])['recordsFiltered'] === 0);
$assert($repo->contratosPaginados(['search' => ['value' => "' OR 1=1 --"]])['recordsFiltered'] === 0);
echo "OK: 23 contratos ficticios, paginas distintas, filtros combinados, contagens e busca parametrizada.\n";
