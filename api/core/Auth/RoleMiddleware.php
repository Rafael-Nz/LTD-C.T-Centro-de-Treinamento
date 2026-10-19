<?php

namespace Core\Auth;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public static function allow(string ...$roles): self
    {
        return new self($roles);
    }

    public static function admin(): self
    {
        return new self(['admin']);
    }

    public static function staff(): self
    {
        return new self(['admin', 'funcionario']);
    }

    public function handle(): bool
    {
        Auth::check();

        if (empty($this->allowedRoles)) {
            return true;
        }

        $userTipo = Auth::user('tipo');

        if (!$userTipo || !in_array($userTipo, $this->allowedRoles, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Acesso negado. Seu perfil não tem permissão para acessar este recurso.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return true;
    }
}
