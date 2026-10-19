<?php
require_once dirname(__DIR__, 2) . '/financeiro/financeiro_bootstrap.php';

$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->sqliteCreateFunction('CONCAT', fn (...$values) => implode('', $values));
$db->exec('
    CREATE TABLE usuario (id INTEGER PRIMARY KEY, nome TEXT, sobrenome TEXT, cpf TEXT, email TEXT);
    CREATE TABLE contrato (id INTEGER PRIMARY KEY, aluno_id INTEGER, multa_percentual NUMERIC, juros_percentual NUMERIC);
    CREATE TABLE cobranca (id INTEGER PRIMARY KEY, contrato_id INTEGER, aluno_id INTEGER, descricao TEXT, competencia TEXT, data_vencimento TEXT, status TEXT, valor_original NUMERIC, desconto NUMERIC, multa NUMERIC, juros NUMERIC, valor_final NUMERIC);
    CREATE TABLE pagamento (id INTEGER PRIMARY KEY, cobranca_id INTEGER, valor_pago NUMERIC, data_pagamento TEXT, registrado_por INTEGER, estornado_em TEXT);

    INSERT INTO usuario VALUES (1, "Carlos", "Silva", "12345678901", "carlos@teste.com");
    INSERT INTO contrato VALUES (10, 1, 2.0, 1.0);

    -- Cobrança 1: paga (R$ 100, paga integralmente)
    INSERT INTO cobranca VALUES (1, 10, 1, "Mensalidade Setembro", "2026-09", "2026-09-10", "paga", 100, 0, 0, 0, 100);
    INSERT INTO pagamento VALUES (1, 1, 100, "2026-09-08", 1, NULL);

    -- Cobrança 2: aberta (R$ 150, com R$ 50 pago) -> Saldo R$ 100
    INSERT INTO cobranca VALUES (2, 10, 1, "Mensalidade Outubro", "2026-10", "2026-10-10", "aberta", 150, 0, 0, 0, 150);
    INSERT INTO pagamento VALUES (2, 2, 50, "2026-10-05", 1, NULL);

    -- Cobrança 3: vencida (R$ 200) -> Saldo R$ 200
    INSERT INTO cobranca VALUES (3, 10, 1, "Mensalidade Agosto", "2026-08", "2026-08-10", "vencida", 200, 0, 0, 0, 200);

    -- Cobrança 4: cancelada
    INSERT INTO cobranca VALUES (4, 10, 1, "Cancelada", "2026-07", "2026-07-10", "cancelada", 120, 0, 0, 0, 120);
');

(new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
$repo = new \Financeiro\FinanceiroRepository();
$service = new \Financeiro\FinanceiroService();
(new ReflectionProperty(\Financeiro\FinanceiroService::class, 'repo'))->setValue($service, $repo);

$assert = function (bool $ok, string $msg) {
    if (!$ok) throw new RuntimeException("Falha no teste: $msg");
};

// 1. Testar métricas gerais
$metricasGerais = $repo->metricas();
$assert($metricasGerais['total_cobrancas'] === 4, 'Total de cobranças geral incorreto');
$assert($metricasGerais['total_aberto'] === 100.0, 'Total em aberto incorreto: ' . $metricasGerais['total_aberto']);
$assert($metricasGerais['total_vencido'] === 200.0, 'Total vencido incorreto: ' . $metricasGerais['total_vencido']);
$assert($metricasGerais['total_pago'] === 150.0, 'Total pago incorreto: ' . $metricasGerais['total_pago']);

// 2. Testar métricas filtradas por competência
$metricasSet = $repo->metricas('2026-09');
$assert($metricasSet['total_cobrancas'] === 1, 'Total de cobranças de 2026-09 incorreto');
$assert($metricasSet['total_pago'] === 100.0, 'Total pago em 2026-09 incorreto');
$assert($metricasSet['total_aberto'] === 0.0, 'Total aberto em 2026-09 incorreto');

// 3. Testar competências disponíveis
$competencias = $repo->competenciasDisponiveis();
$assert(in_array('2026-10', $competencias, true) && in_array('2026-09', $competencias, true), 'Competências disponíveis incorretas');

// 4. Testar cálculo de encargos (multa 2%, juros 1% ao mês)
// Vencimento: 2026-08-10, Referência: 2026-08-20 (10 dias de atraso)
// Valor base: 200.00
// Multa: 200 * 0.02 = 4.00
// Juros: 200 * (0.01 / 30) * 10 = 0.666... -> 0.67
$cobrancaVencida = [
    'data_vencimento' => '2026-08-10',
    'valor_original' => 200.00,
    'desconto' => 0.00,
    'valor_final' => 200.00,
    'multa_percentual' => 2.0,
    'juros_percentual' => 1.0,
];
$encargos = $service->calcularEncargos($cobrancaVencida, '2026-08-20');
$assert($encargos['dias_atraso'] === 10, 'Dias de atraso incorretos: ' . $encargos['dias_atraso']);
$assert($encargos['multa'] === 4.0, 'Multa calculada incorreta: ' . $encargos['multa']);
$assert($encargos['juros'] === 0.67, 'Juros calculados incorretos: ' . $encargos['juros']);
$assert($encargos['valor_total_atualizado'] === 204.67, 'Total atualizado incorreto: ' . $encargos['valor_total_atualizado']);

// Cobrança em dia (sem encargos)
$encargosEmDia = $service->calcularEncargos($cobrancaVencida, '2026-08-10');
$assert($encargosEmDia['valor_encargos'] === 0.0, 'Cobrança no vencimento não deveria ter encargos');

// 5. Testar aplicação de encargos
$repo->aplicarEncargos(3, 4.00, 0.67);
$atualizada = $repo->cobranca(3);
$assert((float) $atualizada['valor_final'] === 204.67, 'Valor final após aplicação de encargos incorreto');

echo "OK: métricas agregadas por período, competências disponíveis e cálculo de encargos por atraso aprovados.\n";
