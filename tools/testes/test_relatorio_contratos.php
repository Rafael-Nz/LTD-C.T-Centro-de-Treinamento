<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require __DIR__ . '/../../autoload.php';

use Relatorio\RelatorioService;
use Relatorio\RelatorioRepository;

echo "=== TESTE: RELATÓRIO DE CONTRATOS SEPARADOS (APENAS PLANO / APENAS SERVIÇO) ===\n\n";

$service = new RelatorioService();
$repo = new RelatorioRepository();

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $message): void {
    global $passed, $failed;
    if ($condition) {
        echo " [OK] {$message}\n";
        $passed++;
    } else {
        echo " [FALHA] {$message}\n";
        $failed++;
    }
}

// 1. Teste de geração: Apenas Planos (contratos_planos)
try {
    $resPlanos = $service->gerar('contratos_planos', []);
    assertCondition($resPlanos['tipo'] === 'contratos_planos', "Retorna tipo 'contratos_planos'");
    assertCondition(is_array($resPlanos['registros']), "Retorna array de registros de planos");
    assertCondition(count($resPlanos['registros']) > 0, "Possui contratos de planos cadastrados (" . count($resPlanos['registros']) . " encontrados)");

    if (!empty($resPlanos['registros'])) {
        $first = $resPlanos['registros'][0];
        $expectedPlanosKeys = [
            'contrato_id', 'aluno_id', 'aluno', 'cpf', 'email', 'plano', 'plano_id',
            'periodicidade', 'data_inicio', 'dia_vencimento', 'valor_contratado',
            'desconto', 'valor_liquido', 'status'
        ];
        $allKeysPresent = true;
        foreach ($expectedPlanosKeys as $key) {
            if (!array_key_exists($key, $first)) {
                $allKeysPresent = false;
                echo "   Chave faltando no plano: {$key}\n";
            }
        }
        assertCondition($allKeysPresent, "Todos os campos esperados de Planos estão presentes");
        assertCondition(!empty($first['plano']), "Contrato possui plano associado ({$first['plano']})");
    }
} catch (\Throwable $e) {
    assertCondition(false, "Falha ao gerar contratos_planos: " . $e->getMessage());
}

// 2. Teste de geração: Apenas Serviços Avulsos (contratos_servicos)
try {
    $resServicos = $service->gerar('contratos_servicos', []);
    assertCondition($resServicos['tipo'] === 'contratos_servicos', "Retorna tipo 'contratos_servicos'");
    assertCondition(is_array($resServicos['registros']), "Retorna array de registros de serviços avulsos");
    assertCondition(count($resServicos['registros']) > 0, "Possui itens de serviços avulsos (" . count($resServicos['registros']) . " encontrados)");

    if (!empty($resServicos['registros'])) {
        $firstServ = $resServicos['registros'][0];
        $expectedServKeys = [
            'item_id', 'contrato_id', 'aluno_id', 'aluno', 'cpf', 'email',
            'servico', 'servico_id', 'quantidade', 'valor_unitario', 'valor_desconto',
            'valor_total', 'data_inicio', 'dia_vencimento', 'status', 'origem_contrato'
        ];
        $allServKeys = true;
        foreach ($expectedServKeys as $key) {
            if (!array_key_exists($key, $firstServ)) {
                $allServKeys = false;
                echo "   Chave faltando no serviço: {$key}\n";
            }
        }
        assertCondition($allServKeys, "Todos os campos esperados de Serviços Avulsos estão presentes");
        assertCondition(!empty($firstServ['servico']), "Item possui nome de serviço ({$firstServ['servico']})");
    }
} catch (\Throwable $e) {
    assertCondition(false, "Falha ao gerar contratos_servicos: " . $e->getMessage());
}

// 3. Teste de delegação: 'contratos' com tipoContrato='plano' e tipoContrato='servico'
try {
    $resDelegPlano = $service->gerar('contratos', ['tipoContrato' => 'plano']);
    assertCondition(count($resDelegPlano['registros']) === count($resPlanos['registros']), "tipo='contratos' com tipoContrato='plano' retorna contratos de planos");

    $resDelegServ = $service->gerar('contratos', ['tipoContrato' => 'servico']);
    assertCondition(count($resDelegServ['registros']) === count($resServicos['registros']), "tipo='contratos' com tipoContrato='servico' retorna contratos de serviços avulsos");
} catch (\Throwable $e) {
    assertCondition(false, "Falha na delegação de tipoContrato: " . $e->getMessage());
}

// 4. Testes de Filtros para Planos
try {
    $resPlanoFiltro = $service->gerar('contratos_planos', ['plano' => '1']);
    assertCondition(count($resPlanoFiltro['registros']) > 0, "Filtro por plano ID 1 funciona em contratos_planos");

    $resStatusPlano = $service->gerar('contratos_planos', ['status' => 'ativo']);
    $allAtivos = true;
    foreach ($resStatusPlano['registros'] as $r) {
        if ($r['status'] !== 'ativo') { $allAtivos = false; break; }
    }
    assertCondition($allAtivos && count($resStatusPlano['registros']) > 0, "Filtro status='ativo' em contratos_planos funciona");
} catch (\Throwable $e) {
    assertCondition(false, "Falha nos filtros de planos: " . $e->getMessage());
}

// 5. Testes de Filtros para Serviços Avulsos
try {
    $resServFiltro = $service->gerar('contratos_servicos', ['servico' => '5']);
    assertCondition(count($resServFiltro['registros']) > 0, "Filtro por serviço ID 5 (Taxa de matrícula) funciona em contratos_servicos");
} catch (\Throwable $e) {
    assertCondition(false, "Falha nos filtros de serviços: " . $e->getMessage());
}

echo "\n--- RESUMO ---\n";
echo "Total de verificações: " . ($passed + $failed) . "\n";
echo "Passou: {$passed}\n";
echo "Falhou: {$failed}\n";

if ($failed === 0) {
    echo "\n>>> TODOS OS TESTES PASSARAM COM SUCESSO! <<<\n";
    exit(0);
} else {
    echo "\n>>> ALGUNS TESTES FALHARAM! <<<\n";
    exit(1);
}
