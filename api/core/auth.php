<?php
namespace Core;

class Auth {

    private static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function check(): void {
        self::start();

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Não autenticado'
            ]);
            exit;
        }
    }

    public static function user(): ?array {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function can(string $permission): bool {
        self::start();
        return in_array($permission, $_SESSION['user']['permissoes'] ?? []);
    }

    public static function login(array $user): void {
        self::start();
        $_SESSION['user'] = $user;
    }

    public static function logout(): void {
        self::start();
        session_destroy();
    }
}