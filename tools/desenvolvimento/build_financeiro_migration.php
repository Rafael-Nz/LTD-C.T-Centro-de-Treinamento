<?php
// Mantem os gatilhos da instalacao e da migracao com os mesmos campos financeiros.
if (PHP_SAPI !== 'cli') exit;
$projectRoot = dirname(__DIR__, 2);
$path = $projectRoot . '/docs/sql/setup/banco.sql';
$sql = file_get_contents($path);
$fields = [
    'contrato' => ['periodicidade', 'geracao_automatica'],
    'cobranca' => ['cancelada_em', 'cancelada_por', 'motivo_cancelamento'],
    'pagamento' => ['estornado_em', 'estornado_por', 'motivo_estorno'],
];
$blocks = [];
$sql = preg_replace_callback('/DROP TRIGGER IF EXISTS audit_(contrato|cobranca|pagamento)_(insert|update|delete)\$\$.*?END\$\$/s', function ($match) use ($fields, &$blocks) {
    $block = $match[0];
    foreach ($fields[$match[1]] as $field) {
        if (str_contains($block, "'$field'")) continue;
        $block = preg_replace_callback('/JSON_OBJECT\((.*?)\)/s', function ($object) use ($field) {
            $side = str_contains($object[1], 'OLD.') ? 'OLD' : 'NEW';
            return 'JSON_OBJECT(' . $object[1] . ", '$field', $side.`$field`)";
        }, $block);
        if ($match[2] === 'update') {
            $block = preg_replace('/ THEN/', " OR NOT (BINARY OLD.`$field` <=> BINARY NEW.`$field`) THEN", $block, 1);
        }
    }
    if ($match[1] === 'pagamento' && $match[2] === 'update' && !str_contains($block, 'payment_reversed')) {
        $block = str_replace("'payment_updated', 'update'", "IF(OLD.estornado_em IS NULL AND NEW.estornado_em IS NOT NULL, 'payment_reversed', 'payment_updated'), 'update'", $block);
    }
    $blocks[] = $block;
    return $block;
}, $sql);
file_put_contents($path, $sql);
$auditPath = $projectRoot . '/docs/sql/auditoria_operacoes.sql';
$auditSql = file_get_contents($auditPath);
foreach ($blocks as $block) {
    preg_match('/DROP TRIGGER IF EXISTS (\w+)\$\$/', $block, $name);
    $auditSql = preg_replace_callback('/DROP TRIGGER IF EXISTS ' . $name[1] . '\$\$.*?END\$\$/s', fn () => $block, $auditSql);
}
file_put_contents($auditPath, $auditSql);
$migration = $projectRoot . '/docs/sql/migrations/financeiro_seguranca.sql';
$base = explode('-- Gatilhos financeiros', file_get_contents($migration))[0];
file_put_contents($migration, rtrim($base) . "\n\n-- Gatilhos financeiros\nDELIMITER $$\n" . implode("\n\n", $blocks) . "\nDELIMITER ;\n");
echo "Gatilhos financeiros atualizados.\n";
