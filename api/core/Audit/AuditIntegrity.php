<?php
namespace Core\Audit;

use PDO;
use RuntimeException;
use Throwable;

final class AuditIntegrity {
    // Ordem e codificacao sao parte do formato v1, compartilhado com o trigger SQL.
    public const FIELDS = ['hash_version', 'chain_id', 'id', 'previous_hash', 'user_id', 'user_name',
        'action', 'operation', 'module', 'entity_type', 'entity_id', 'old_data', 'new_data',
        'ip_address', 'user_agent', 'request_id', 'correlation_id', 'http_method', 'route',
        'context_data', 'result', 'http_status', 'created_at'];
    public const GENESIS = '0000000000000000000000000000000000000000000000000000000000000000';

    public static function hash(array $row): string {
        $payload = 'audit-v1|';
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $row)) throw new RuntimeException("Campo ausente: $field");
            $payload .= $row[$field] === null ? 'N' : 'S' . hash('sha256', (string) $row[$field]);
        }
        return hash('sha256', $payload);
    }

    /** Verifica um snapshot consistente; checkpoint opcional deve vir de armazenamento confiavel externo. */
    public static function verify(PDO $db, ?array $checkpoint = null): array {
        if ($db->inTransaction()) throw new RuntimeException('Verificacao requer conexao sem transacao aberta.');
        $db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $db->beginTransaction();
        try {
            $head = $db->query('SELECT chain_id, last_id, last_hash FROM audit_chain_state WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
            if (!$head) throw new RuntimeException('Cabeca da cadeia ausente.');
            $previous = self::GENESIS;
            $id = 0;
            $anchorFound = $checkpoint === null;
            if ($checkpoint !== null) {
                if (!isset($checkpoint['chain_id'], $checkpoint['last_id'], $checkpoint['last_hash'])
                    || !ctype_digit((string) $checkpoint['last_id'])
                    || !preg_match('/^[a-f0-9]{64}$/D', $checkpoint['last_hash'])
                    || $checkpoint['chain_id'] !== $head['chain_id']) {
                    throw new RuntimeException('Checkpoint invalido ou cadeia substituida.');
                }
                $anchorFound = (string) $checkpoint['last_id'] === '0' && $checkpoint['last_hash'] === self::GENESIS;
            }
            // Paginacao dentro do mesmo snapshot limita memoria, sem perder consistencia.
            do {
                $stmt = $db->prepare('SELECT * FROM audit_logs WHERE id > ? ORDER BY id LIMIT 1000');
                $stmt->execute([$id]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    if ((string) $row['id'] !== (string) ($id + 1) || (int) $row['hash_version'] !== 1
                        || $row['chain_id'] !== $head['chain_id'] || $row['previous_hash'] !== $previous
                        || !hash_equals(self::hash($row), $row['row_hash'])) {
                        throw new RuntimeException('Integridade violada no registro #' . $row['id']);
                    }
                    $id++;
                    $previous = $row['row_hash'];
                    if ($checkpoint !== null && (string) $checkpoint['last_id'] === (string) $id) {
                        if (!hash_equals($checkpoint['last_hash'], $previous)) throw new RuntimeException('Checkpoint divergente no registro #' . $id);
                        $anchorFound = true;
                    }
                }
            } while (count($rows) === 1000);
            if ((string) $head['last_id'] !== (string) $id || $head['last_hash'] !== $previous || !$anchorFound) {
                throw new RuntimeException('Final da cadeia ausente, truncado ou divergente do checkpoint.');
            }
            $db->commit();
            return ['chain_id' => $head['chain_id'], 'last_id' => (string) $id, 'last_hash' => $previous];
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
