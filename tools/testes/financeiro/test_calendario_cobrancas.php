<?php
require_once dirname(__DIR__, 2) . '/financeiro/financeiro_bootstrap.php';
use Financeiro\FinanceiroService as S;
$base = ['data_inicio' => '2026-01-15', 'dia_vencimento' => 10, 'data_fim' => null, 'periodicidade' => 'mensal'];
$limite = new DateTimeImmutable('2026-12-31');
$cases = [
    ['mensal', 11, '2026-12-10'],
    ['trimestral', 4, '2026-11-10'],
    ['semestral', 2, '2026-08-10'],
    ['anual', 1, '2026-02-10'],
    ['avulso', 1, '2026-02-10'],
];
foreach ($cases as [$periodo, $count, $last]) {
    $dates = S::vencimentos(array_replace($base, ['periodicidade' => $periodo]), $limite);
    if (count($dates) !== $count || $dates[0] !== '2026-02-10' || end($dates) !== $last || count(array_unique($dates)) !== $count) throw new RuntimeException($periodo);
}
if (S::vencimentos(array_replace($base, ['data_fim' => '2026-03-09']), $limite) !== ['2026-02-10']) throw new RuntimeException('Vigência final');
if (S::vencimentos($base, new DateTimeImmutable('2026-01-31')) !== []) throw new RuntimeException('Início futuro');
if (S::vencimentos(array_replace($base, ['data_inicio' => '2026-01-10', 'data_fim' => '2026-01-10']), $limite) !== ['2026-01-10']) throw new RuntimeException('Limites inclusivos');
echo "OK: cinco periodicidades, vencimento, limites de vigência e datas únicas.\n";
