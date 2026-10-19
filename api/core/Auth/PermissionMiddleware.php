<?php

namespace Core\Auth;

class PermissionMiddleware
{
    private array $requiredPermissions;
    private bool $requireAll;

    public function __construct(array $permissions = [], bool $requireAll = false)
    {
        $this->requiredPermissions = $permissions;
        $this->requireAll = $requireAll;
    }

    public static function require(string ...$permissions): self
    {
        return new self($permissions, true);
    }

    public static function any(string ...$permissions): self
    {
        return new self($permissions, false);
    }

    public function handle(): bool
    {
        Auth::check();

        if (empty($this->requiredPermissions)) {
            return true;
        }

        if (Auth::user('tipo') === 'admin') {
            return true;
        }

        $allowed = $this->requireAll;

        if ($this->requireAll) {
            foreach ($this->requiredPermissions as $perm) {
                if (!Auth::can($perm)) {
                    $allowed = false;
                    break;
                }
            }
        } else {
            $allowed = false;
            foreach ($this->requiredPermissions as $perm) {
                if (Auth::can($perm)) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Acesso negado. Você não possui a permissão necessária para acessar este recurso.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return true;
    }

    public function __invoke(): bool
    {
        return $this->handle();
    }
}
