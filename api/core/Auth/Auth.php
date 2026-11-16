<?php

namespace Core\Auth;

class AuthGuard
{
    public function __construct(private string $guard) {}

    public function user(?string $key = null)
    {
        return Auth::user($key, $this->guard);
    }

    public function id(): ?int
    {
        return Auth::id($this->guard);
    }

    public function check(): void
    {
        Auth::check($this->guard);
    }

    public function isAuthenticated(): bool
    {
        return Auth::id($this->guard) !== null;
    }

    public function tipo(): ?string
    {
        return Auth::userTipo($this->guard);
    }

    public function isAdmin(): bool
    {
        return Auth::isAdmin($this->guard);
    }

    public function can(string $permission): bool
    {
        return Auth::can($permission, $this->guard);
    }

    public function canAny(array $permissions): bool
    {
        return Auth::canAny($permissions, $this->guard);
    }

    public function logout(): void
    {
        Auth::logout($this->guard);
    }
}

class Auth
{
    public const GUARD_ADMIN = 'admin';
    public const GUARD_ALUNO = 'aluno';

    private static ?string $context = null;
    // Cache apenas desta requisicao; nunca persistir validacao entre requisicoes.
    private static array $validatedGuards = [];

    public static function setContext(string $guard): void
    {
        self::$context = in_array($guard, [self::GUARD_ADMIN, self::GUARD_ALUNO], true)
            ? $guard
            : self::GUARD_ADMIN;
    }

    public static function context(): string
    {
        if (self::$context !== null) {
            return self::$context;
        }

        $headerArea = strtolower(trim($_SERVER['HTTP_X_AUTH_AREA'] ?? ''));
        if (in_array($headerArea, [self::GUARD_ADMIN, self::GUARD_ALUNO], true)) {
            return $headerArea;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/admin')) {
            return self::GUARD_ADMIN;
        }

        // Se houver apenas auth_aluno na sessão e nenhuma auth_admin, infere aluno
        self::start();
        if (empty($_SESSION['auth_admin']) && !empty($_SESSION['auth_aluno'])) {
            return self::GUARD_ALUNO;
        }

        return self::GUARD_ADMIN;
    }

    public static function guard(string $guard): AuthGuard
    {
        return new AuthGuard($guard);
    }

    public static function admin(): AuthGuard
    {
        return new AuthGuard(self::GUARD_ADMIN);
    }

    public static function aluno(): AuthGuard
    {
        return new AuthGuard(self::GUARD_ALUNO);
    }

    public static function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        // X-Forwarded-Proto só é aceito quando vem de um proxy explicitamente confiável
        // (APP_TRUSTED_PROXIES=ip1,ip2); caso contrário o cliente poderia forjá-lo.
        $trusted = array_filter(array_map('trim', explode(',', (string) ($_ENV['APP_TRUSTED_PROXIES'] ?? ''))));
        return $trusted !== []
            && in_array($_SERVER['REMOTE_ADDR'] ?? '', $trusted, true)
            && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public static function getCookiePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return (str_starts_with($uri, '/ctt/') || $uri === '/ctt') ? '/ctt' : '/';
    }

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                ini_set('session.use_strict_mode', '1');
                ini_set('session.use_only_cookies', '1');
                session_name('CTTSESSID');
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => self::getCookiePath(),
                    'secure' => self::isSecure(),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            @session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $hasAuth = !empty($_SESSION['auth_admin']) || !empty($_SESSION['auth_aluno']);
            if ($hasAuth) {
                $maxIdle = 7200; // 2 horas de inatividade
                if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity'] > $maxIdle)) {
                    // Não chamar self::logout() aqui: ele chama start() de volta e, como a sessão
                    // ainda estaria expirada, entraria em recursão infinita (crash do Apache).
                    $_SESSION = [];
                    self::$validatedGuards = [];
                    @session_destroy();
                } else {
                    $_SESSION['_last_activity'] = time();
                }
            }
        }
    }

    public static function login($usuario, ?string $guard = null, ?string $credentialHash = null): void
    {
        self::start();
        if ($guard === null) {
            $guard = (is_array($usuario) && ($usuario['tipo'] ?? null) === self::GUARD_ALUNO)
                ? self::GUARD_ALUNO
                : self::GUARD_ADMIN;
        }

        $id = is_array($usuario) ? (int) ($usuario['id'] ?? 0) : (int) $usuario;
        $guardData = self::loadGuardState($id, $guard);
        if ($guardData === null || ($credentialHash !== null && !hash_equals(
            $guardData['credential_fingerprint'],
            hash('sha256', $guard . '|' . $guardData['user_tipo'] . '|' . $credentialHash)
        ))) {
            throw new \RuntimeException('Conta indisponivel para esta area.');
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
        $_SESSION['auth_' . $guard] = $guardData;
        unset(self::$validatedGuards[$guard]);
        if ($guard === self::context() || empty($_SESSION['user_id'])) {
            self::mirrorGuard($guardData);
        }
    }

    public static function user(?string $key = null, ?string $guard = null)
    {
        $user = self::currentGuard($guard ?? self::context())['user'] ?? null;

        if (!$user) {
            return null;
        }

        if ($key) {
            return $user[$key] ?? null;
        }

        return $user;
    }

    public static function check(?string $guard = null): void
    {
        self::start();
        $targetGuard = $guard ?? self::context();
        $id = self::id($targetGuard);

        if (empty($id)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sessao expirada ou nao encontrada.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function logout(?string $guard = null): void
    {
        self::start();

        if ($guard !== null) {
            self::forgetGuard($guard);

            // Se nenhum outro guard estiver ativo, encerra a sessão PHP por completo e expira o cookie
            if (empty($_SESSION['auth_admin']) && empty($_SESSION['auth_aluno'])) {
                $_SESSION = [];
                if (ini_get('session.use_cookies') && !headers_sent()) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
                }
                if (session_status() === PHP_SESSION_ACTIVE) {
                    @session_destroy();
                }
            }
            return;
        }

        // Sem guard específico: encerra completamente
        $_SESSION = [];
        self::$validatedGuards = [];
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function id(?string $guard = null): ?int
    {
        return self::currentGuard($guard ?? self::context())['user_id'] ?? null;
    }

    public static function isAdmin(?string $guard = self::GUARD_ADMIN): bool
    {
        return self::userTipo($guard) === 'admin';
    }

    public static function userTipo(?string $guard = null): ?string
    {
        return self::currentGuard($guard ?? self::context())['user_tipo'] ?? null;
    }

    public static function cargoId(?string $guard = self::GUARD_ADMIN): ?int
    {
        return self::currentGuard($guard ?? self::context())['user_cargo_id'] ?? null;
    }

    public static function cargoNome(?string $guard = self::GUARD_ADMIN): ?string
    {
        return self::currentGuard($guard ?? self::context())['user_cargo_nome'] ?? null;
    }

    public static function permissions(?string $guard = self::GUARD_ADMIN): array
    {
        return self::currentGuard($guard ?? self::context())['user_permissions'] ?? [];
    }

    private static function loadGuardState(int $id, string $guard): ?array
    {
        if ($id < 1 || !in_array($guard, [self::GUARD_ADMIN, self::GUARD_ALUNO], true)) {
            return null;
        }
        $repo = new \Usuario\UsuarioRepository();
        $state = $repo->findSessionState($id);
        if (!$state || !(bool) $state['ativo']) return null;

        $type = $guard === self::GUARD_ALUNO ? 'aluno'
            : ($state['tipo_usuario'] === 'admin' ? 'admin' : 'funcionario');
        if (($type === 'aluno' && empty($state['aluno_id']))
            || ($type === 'funcionario' && (empty($state['funcionario_id'])
                || ($state['cargo_id'] !== null && !(bool) $state['cargo_ativo'])))) {
            return null;
        }
        $passwordHash = $state['senha_' . $type] ?? '';
        if (!is_string($passwordHash) || $passwordHash === '') return null;
        $cargoId = $type === 'funcionario' && $state['cargo_id'] !== null ? (int) $state['cargo_id'] : null;
        $permissions = $type === 'admin' ? ['*']
            : ($cargoId !== null ? $repo->findPermissionsByCargoId($cargoId) : []);
        $user = [
            'id' => (int) $state['id'], 'nome' => $state['nome'], 'tipo' => $type,
            'cargo_id' => $cargoId,
            'cargo_nome' => $type === 'admin' ? 'Administrador' : ($type === 'aluno' ? 'Aluno' : $state['cargo_nome']),
            'permissoes' => $permissions,
        ];
        return [
            'user_id' => $user['id'], 'user_nome' => $user['nome'], 'user_tipo' => $type,
            'user_cargo_id' => $cargoId, 'user_cargo_nome' => $user['cargo_nome'],
            'user_permissions' => $permissions, 'user' => $user,
            'credential_fingerprint' => hash('sha256', $guard . '|' . $type . '|' . $passwordHash),
        ];
    }

    private static function currentGuard(string $guard): ?array
    {
        self::start();
        $data = $_SESSION['auth_' . $guard] ?? null;
        // Sessoes antigas sem vinculo com a credencial devem autenticar novamente.
        if (!is_array($data) || empty($data['credential_fingerprint']) || empty($data['user_id'])) {
            self::forgetGuard($guard);
            return null;
        }
        if ((self::$validatedGuards[$guard] ?? null) === $data) return $data;
        try {
            $fresh = self::loadGuardState((int) $data['user_id'], $guard);
        } catch (\Throwable $e) {
            error_log('[Auth] Nao foi possivel revalidar a sessao.');
            $fresh = null;
        }
        if ($fresh === null || !hash_equals($fresh['credential_fingerprint'], (string) $data['credential_fingerprint'])) {
            self::forgetGuard($guard);
            return null;
        }
        $_SESSION['auth_' . $guard] = $fresh;
        self::$validatedGuards[$guard] = $fresh;
        if (($_SESSION['user_id'] ?? null) === $fresh['user_id']
            && ($_SESSION['user_tipo'] ?? null) === $fresh['user_tipo']) {
            self::mirrorGuard($fresh);
        }
        return $fresh;
    }

    private static function mirrorGuard(array $data): void
    {
        foreach (['user_id', 'user_nome', 'user_tipo', 'user_cargo_id', 'user_cargo_nome', 'user_permissions', 'user'] as $key) {
            $_SESSION[$key] = $data[$key];
        }
    }

    private static function forgetGuard(string $guard): void
    {
        unset($_SESSION['auth_' . $guard], self::$validatedGuards[$guard]);
        $rootTipo = $_SESSION['user_tipo'] ?? ($_SESSION['user']['tipo'] ?? null);
        if (($guard === self::GUARD_ALUNO && $rootTipo === 'aluno')
            || ($guard === self::GUARD_ADMIN && in_array($rootTipo, ['admin', 'funcionario'], true))) {
            foreach (['user_id', 'user_nome', 'user_tipo', 'user_cargo_id', 'user_cargo_nome', 'user_permissions', 'user'] as $key) {
                unset($_SESSION[$key]);
            }
        }
    }

    public static function can(string $permission, ?string $guard = self::GUARD_ADMIN): bool
    {
        self::start();
        if (self::isAdmin($guard)) {
            return true;
        }

        $perms = self::permissions($guard);
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public static function canAny(array $permissions, ?string $guard = self::GUARD_ADMIN): bool
    {
        self::start();
        if (self::isAdmin($guard)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (self::can($permission, $guard)) {
                return true;
            }
        }

        return false;
    }
}
