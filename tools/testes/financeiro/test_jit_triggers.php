<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/financeiro/financeiro_bootstrap.php';

use Core\Database\Database;
use Financeiro\FinanceiroService;
use Financeiro\FinanceiroRepository;

echo "== Teste de Triggers e Just-In-Time Financeiro ==\n";

$db = Database::getConnection();

// 1. Teste de Trigger: Inserção de cobrança vencida com status aberta
$aluno = $db->query("SELECT usuario_id FROM aluno LIMIT 1")->fetchColumn();
$contrato = $db->query("SELECT id FROM contrato WHERE status = 'ativo' LIMIT 1")->fetchColumn();

if ($aluno && $contrato) {
    // Insere cobrança com data de vencimento no passado e status 'aberta'
    $stmt = $db->prepare("INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
        VALUES (?, ?, '1999-01', 'Teste Trigger Vencida', 100.00, 0, 0, 0, 100.00, '1999-01-10', 'aberta')");
    $stmt->execute([$contrato, $aluno]);
    $id = (int) $db->lastInsertId();

    $statusInserido = $db->query("SELECT status FROM cobranca WHERE id = {$id}")->fetchColumn();
    if ($statusInserido !== 'vencida') {
        throw new RuntimeException("Falha na trigger: esperava status 'vencida', obteve '{$statusInserido}'");
    }
    echo "OK: Trigger BEFORE INSERT transformou automaticamente status 'aberta' em 'vencida'.\n";

    // Atualização com data futura para aberta, depois retroage para passada
    $db->exec("UPDATE cobranca SET data_vencimento = '2099-01-10', status = 'aberta' WHERE id = {$id}");
    $db->exec("UPDATE cobranca SET data_vencimento = '1999-01-10' WHERE id = {$id}");
    $statusAtualizado = $db->query("SELECT status FROM cobranca WHERE id = {$id}")->fetchColumn();
    if ($statusAtualizado !== 'vencida') {
        throw new RuntimeException("Falha na trigger: esperava status 'vencida' apos update, obteve '{$statusAtualizado}'");
    }
    echo "OK: Trigger BEFORE UPDATE manteve e converteu status 'aberta' em 'vencida' em retroação de data.\n";

    // Limpa registro de teste
    $db->exec("DELETE FROM cobranca WHERE id = {$id}");
} else {
    echo "Aviso: Nenhum aluno/contrato encontrado para teste direto de INSERT, pulando teste de inserção real.\n";
}

// 2. Teste do Just-in-Time
$service = new FinanceiroService();
$stateFile = dirname(__DIR__, 3) . '/tools/financeiro/financeiro_jit_state.json';
if (is_file($stateFile)) {
    unlink($stateFile);
}

// Primeira execução do JIT (deve executar a geração)
$res1 = $service->processarJustInTime();
if (!$res1['executou_geracao']) {
    throw new RuntimeException("JIT deveria ter executado na primeira chamada.");
}
if (!is_file($stateFile)) {
    throw new RuntimeException("Arquivo de estado JIT não foi criado.");
}
echo "OK: Primeira execução do Just-In-Time disparou geração e salvou estado.\n";

// Segunda execução do JIT no mesmo dia (deve utilizar throttle/cache)
$res2 = $service->processarJustInTime();
if ($res2['executou_geracao']) {
    throw new RuntimeException("JIT não deveria ter reexecutado a geração no mesmo dia.");
}
echo "OK: Segunda chamada utilizou throttle do Just-In-Time com sucesso.\n";

echo "\nTODOS OS TESTES DE TRIGGERS E JUST-IN-TIME FORAM APROVADOS COM SUCESSO!\n";
