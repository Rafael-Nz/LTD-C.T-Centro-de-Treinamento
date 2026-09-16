<?php
require_once __DIR__ . '/financeiro_bootstrap.php';
try {
    $db = \Core\Database\Database::getConnection();
    $db->query('SELECT periodicidade, geracao_automatica FROM contrato LIMIT 0');
    $db->query('SELECT cancelada_em, cancelada_por, motivo_cancelamento FROM cobranca LIMIT 0');
    $db->query('SELECT chave_idempotencia, requisicao_hash, estornado_em, transacao_unica FROM pagamento LIMIT 0');
    echo "Estrutura financeira atualizada.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Atualizacao pendente. Aplique docs/sql/migrations/financeiro_seguranca.sql como administrador do banco.\n");
    exit(1);
}
