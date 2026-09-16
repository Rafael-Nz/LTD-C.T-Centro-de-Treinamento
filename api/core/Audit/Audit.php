<?php
namespace Core\Audit;

use Core\Database\Database;
use PDO;
use Throwable;

/** Auditoria de requisicoes na mesma cadeia das operacoes de negocio. */
final class Audit {
    private static ?array $context = null;
    private static bool $finished = true;

    public static function begin(string $method, string $route, array $params): void {
        self::$finished = true;
        $ids = [];
        foreach ($params as $key => $value) {
            if (ctype_digit((string) $value) && strlen((string) $value) <= 10) $ids[$key] = (int) $value;
        }
        $requestId = bin2hex(random_bytes(16));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        self::$context = [
            'request_id' => $requestId,
            // Uma requisicao e sua unidade de correlacao; nunca confiar em ID do cliente.
            'correlation_id' => $requestId,
            'http_method' => $method, 'route' => $route,
            'context_data' => ['params' => $ids],
            'user_id' => self::sessionActor(),
            'ip_address' => filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null,
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512, 'UTF-8'),
        ];
        header('X-Request-ID: ' . $requestId);
        header('X-Correlation-ID: ' . $requestId);
        try {
            $db = Database::getConnection();
            self::$context['user_name'] = self::actorName($db, self::$context['user_id']);
            if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $db->query('SELECT hash_version, row_hash FROM audit_logs LIMIT 0');
                $stmt = $db->prepare('SET @audit_user_id = ?, @audit_user_name = ?, @audit_ip = ?,
                    @audit_user_agent = ?, @audit_request_id = ?, @audit_correlation_id = ?,
                    @audit_http_method = ?, @audit_route = ?, @audit_context_data = ?');
                $stmt->execute([
                    self::$context['user_id'], self::$context['user_name'], self::$context['ip_address'],
                    self::$context['user_agent'], $requestId, $requestId, $method, $route,
                    self::json(self::$context['context_data']),
                ]);
            }
            if (!in_array($method, ['GET', 'HEAD'], true)) self::record('request_started', 'pending', null, $db);
            self::$finished = false;
        } catch (Throwable $e) {
            self::fallback('auditoria_indisponivel');
            throw $e;
        }
        register_shutdown_function([self::class, 'finish']);
    }

    public static function responseId($data, int $status): void {
        if (self::$context !== null && $status < 400 && is_array($data)
            && isset($data['id']) && ctype_digit((string) $data['id'])) {
            self::$context['context_data']['response_id'] = (int) $data['id'];
        }
    }

    public static function transaction(PDO $db): void {
        if (self::$context !== null && !self::$finished) self::record('transaction_committed', 'success', null, $db);
    }

    public static function lockChain(PDO $db): void {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            // Ordem consistente nos servicos: cabeca antes dos registros de negocio.
            $row = $db->query('SELECT id FROM audit_chain_state WHERE id = 1 FOR UPDATE')->fetchColumn();
            if ($row === false) throw new \RuntimeException('Cabeca da auditoria ausente.');
        }
    }

    public static function finish(): void {
        if (self::$context === null || self::$finished) return;
        self::$finished = true;
        $lastError = error_get_last();
        $fatal = $lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true);
        $status = $fatal ? 500 : (http_response_code() ?: 200);
        try {
            $db = Database::getConnection();
            if ($db->inTransaction()) {
                self::fallback('requisicao_finalizada_com_transacao_aberta');
                return;
            }
            $route = self::$context['route'];
            if ($route === '/auth/login' && $status < 400) {
                self::$context['user_id'] = self::sessionActor();
                self::$context['user_name'] = self::actorName($db, self::$context['user_id']);
            }
            $action = match ($route) {
                '/auth/login' => $status < 400 ? 'login' : 'login_failed',
                '/auth/logout' => $status < 400 ? 'logout' : 'logout_failed',
                default => $status < 400 ? 'request_completed' : 'operation_failed',
            };
            if ($status >= 400 || !in_array(self::$context['http_method'], ['GET', 'HEAD'], true)) {
                self::record($action, $status < 400 ? 'success' : 'failure', $status, $db);
            }
        } catch (Throwable $e) {
            self::fallback('falha_ao_registrar_resultado');
        }
    }

    private static function sessionActor(): ?int {
        $id = $_SESSION['user_id'] ?? null;
        return is_numeric($id) && (int) $id > 0 ? (int) $id : null;
    }

    private static function actorName(PDO $db, ?int $id): ?string {
        if ($id === null) return null;
        $stmt = $db->prepare('SELECT nome, sobrenome FROM usuario WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? trim($user['nome'] . ' ' . $user['sobrenome']) : 'Usuario nao encontrado';
    }

    private static function json(array $data): string {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function record(string $action, string $result, ?int $status, PDO $db): void {
        $c = self::$context;
        $auth = str_starts_with($c['route'], '/auth/');
        $stmt = $db->prepare('INSERT INTO audit_logs
            (user_id, user_name, action, operation, module, entity_type, entity_id,
             ip_address, user_agent, request_id, correlation_id, http_method, route, context_data, result, http_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $c['user_id'], $c['user_name'], $action, $auth ? 'auth' : strtolower($c['http_method']),
            $auth ? 'autenticacao' : (explode('/', trim($c['route'], '/'))[0] ?: 'sistema'),
            $auth ? 'usuario' : null, $auth && $result === 'success' ? $c['user_id'] : null,
            $c['ip_address'], $c['user_agent'], $c['request_id'], $c['correlation_id'],
            $c['http_method'], $c['route'], self::json($c['context_data']), $result, $status,
        ]);
    }

    private static function fallback(string $event): void {
        error_log('[AUDITORIA] ' . json_encode([
            'request_id' => self::$context['request_id'] ?? null, 'event' => $event,
        ], JSON_UNESCAPED_UNICODE));
    }
}
