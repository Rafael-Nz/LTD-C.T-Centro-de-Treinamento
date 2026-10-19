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

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                ini_set('session.use_strict_mode', '1');
                ini_set('session.use_only_cookies', '1');
                session_name('CTTSESSID');
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/ctt',
                    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            @session_start();
        }
    }

    public static function login($usuario, ?string $guard = null): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        if ($guard === null) {
            $guard = (is_array($usuario) && ($usuario['tipo'] ?? null) === self::GUARD_ALUNO)
                ? self::GUARD_ALUNO
                : self::GUARD_ADMIN;
        }

        $guardKey = 'auth_' . $guard;

        if (is_array($usuario)) {
            $guardData = [
                'user_id' => (int) ($usuario['id'] ?? 0),
                'user_nome' => $usuario['nome'] ?? '',
                'user_tipo' => $usuario['tipo'] ?? '',
                'user_cargo_id' => isset($usuario['cargo_id']) ? (int) $usuario['cargo_id'] : null,
                'user_cargo_nome' => $usuario['cargo_nome'] ?? null,
                'user_permissions' => $usuario['permissoes'] ?? (($usuario['tipo'] ?? '') === 'admin' ? ['*'] : []),
                'user' => $usuario,
            ];

            $_SESSION[$guardKey] = $guardData;

            // Espelha no nível raiz da sessão para compatibilidade legada se for o contexto ativo
            if ($guard === self::context() || empty($_SESSION['user_id'])) {
                $_SESSION['user_id'] = $guardData['user_id'];
                $_SESSION['user_nome'] = $guardData['user_nome'];
                $_SESSION['user_tipo'] = $guardData['user_tipo'];
                $_SESSION['user_cargo_id'] = $guardData['user_cargo_id'];
                $_SESSION['user_cargo_nome'] = $guardData['user_cargo_nome'];
                $_SESSION['user_permissions'] = $guardData['user_permissions'];
                $_SESSION['user'] = $guardData['user'];
            }
        } else {
            $_SESSION[$guardKey] = [
                'user_id' => (int) $usuario,
                'user' => ['id' => (int) $usuario],
                'user_tipo' => $guard === self::GUARD_ALUNO ? 'aluno' : 'admin'
            ];
            if ($guard === self::context() || empty($_SESSION['user_id'])) {
                $_SESSION['user_id'] = (int) $usuario;
            }
        }
    }

    public static function user(?string $key = null, ?string $guard = null)
    {
        self::start();
        $targetGuard = $guard ?? self::context();
        $guardKey = 'auth_' . $targetGuard;

        $user = $_SESSION[$guardKey]['user'] ?? null;

        // Fallback para raiz da sessão (testes legados ou migração)
        if (!$user && isset($_SESSION['user'])) {
            $rootTipo = $_SESSION['user']['tipo'] ?? ($_SESSION['user_tipo'] ?? null);
            if ($targetGuard === self::GUARD_ALUNO && $rootTipo === 'aluno') {
                $user = $_SESSION['user'];
            } elseif ($targetGuard === self::GUARD_ADMIN && in_array($rootTipo, ['admin', 'funcionario'], true)) {
                $user = $_SESSION['user'];
            }
        }

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
            unset($_SESSION['auth_' . $guard]);

            // Limpa as variáveis de espelhamento raiz associadas a este guard
            $rootTipo = $_SESSION['user_tipo'] ?? ($_SESSION['user']['tipo'] ?? '');
            if (($guard === self::GUARD_ALUNO && $rootTipo === 'aluno') ||
                ($guard === self::GUARD_ADMIN && in_array($rootTipo, ['admin', 'funcionario'], true)) ||
                ($guard === self::context())) {
                unset(
                    $_SESSION['user_id'],
                    $_SESSION['user_nome'],
                    $_SESSION['user_tipo'],
                    $_SESSION['user_cargo_id'],
                    $_SESSION['user_cargo_nome'],
                    $_SESSION['user_permissions'],
                    $_SESSION['user']
                );
            }

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
        self::start();
        $targetGuard = $guard ?? self::context();
        $guardKey = 'auth_' . $targetGuard;

        if (isset($_SESSION[$guardKey]['user_id'])) {
            return (int) $_SESSION[$guardKey]['user_id'];
        }

        // Fallback raiz se compatível com o guard
        if (!empty($_SESSION['user_id'])) {
            $rootTipo = $_SESSION['user_tipo'] ?? null;
            if ($targetGuard === self::GUARD_ALUNO && $rootTipo === 'aluno') {
                return (int) $_SESSION['user_id'];
            }
            if ($targetGuard === self::GUARD_ADMIN && in_array($rootTipo, ['admin', 'funcionario'], true)) {
                return (int) $_SESSION['user_id'];
            }
        }

        return null;
    }

    public static function isAdmin(?string $guard = self::GUARD_ADMIN): bool
    {
        return self::userTipo($guard) === 'admin';
    }

    public static function userTipo(?string $guard = null): ?string
    {
        self::start();
        $targetGuard = $guard ?? self::context();
        $guardKey = 'auth_' . $targetGuard;

        if (isset($_SESSION[$guardKey]['user_tipo'])) {
            return $_SESSION[$guardKey]['user_tipo'];
        }

        if (!empty($_SESSION['user_tipo'])) {
            $rootTipo = $_SESSION['user_tipo'];
            if ($targetGuard === self::GUARD_ALUNO && $rootTipo === 'aluno') {
                return 'aluno';
            }
            if ($targetGuard === self::GUARD_ADMIN && in_array($rootTipo, ['admin', 'funcionario'], true)) {
                return $rootTipo;
            }
        }

        return null;
    }

    public static function cargoId(?string $guard = self::GUARD_ADMIN): ?int
    {
        self::start();
        $guardKey = 'auth_' . $guard;

        if (isset($_SESSION[$guardKey]['user_cargo_id'])) {
            return $_SESSION[$guardKey]['user_cargo_id'] !== null ? (int) $_SESSION[$guardKey]['user_cargo_id'] : null;
        }

        return isset($_SESSION['user_cargo_id']) ? (int) $_SESSION['user_cargo_id'] : null;
    }

    public static function cargoNome(?string $guard = self::GUARD_ADMIN): ?string
    {
        self::start();
        $guardKey = 'auth_' . $guard;

        if (isset($_SESSION[$guardKey]['user_cargo_nome'])) {
            return $_SESSION[$guardKey]['user_cargo_nome'];
        }

        return $_SESSION['user_cargo_nome'] ?? null;
    }

    public static function permissions(?string $guard = self::GUARD_ADMIN): array
    {
        self::start();
        if (self::isAdmin($guard)) {
            return ['*'];
        }

        $guardKey = 'auth_' . $guard;
        if (isset($_SESSION[$guardKey]['user_permissions'])) {
            return $_SESSION[$guardKey]['user_permissions'];
        }

        return $_SESSION['user_permissions'] ?? [];
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
