<?php
require_once __DIR__ . '/financeiro_bootstrap.php';
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
(new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
$repo = new \Financeiro\FinanceiroRepository();
$assert = function ($ok) { if (!$ok) throw new RuntimeException('Falha na paginação do catálogo.'); };
foreach (['servico' => 'tipo', 'plano' => 'periodicidade'] as $table => $category) {
    $db->exec("CREATE TABLE $table (id INTEGER PRIMARY KEY, nome TEXT, descricao TEXT, ativo INTEGER, $category TEXT)");
    for ($i = 1; $i <= 23; $i++) {
        $db->prepare("INSERT INTO $table VALUES (?, ?, ?, ?, ?)")->execute([$i, sprintf('Item %02d', $i), 'Descrição teste', $i % 2, $i % 2 ? 'mensal' : 'outro']);
    }
    $first = $repo->catalogoPaginado($table, ['start' => 0, 'length' => 10]);
    $last = $repo->catalogoPaginado($table, ['start' => 20, 'length' => 10]);
    $assert($first['recordsTotal'] === 23 && count($first['data']) === 10 && count($last['data']) === 3);
    $assert(!array_intersect(array_column($first['data'], 'id'), array_column($last['data'], 'id')));
    $filtered = $repo->catalogoPaginado($table, ['ativo' => '0', 'categoria' => 'outro', 'search' => ['value' => 'Item 0']]);
    $assert($filtered['recordsTotal'] === 23 && $filtered['recordsFiltered'] === 4);
    $assert($repo->catalogoPaginado($table, ['search' => ['value' => "' OR 1=1 --"]])['recordsFiltered'] === 0);
}
echo "OK: serviços e planos com paginação, contagens, filtros combinados e busca parametrizada.\n";
