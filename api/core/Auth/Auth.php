<?php

namespace Core\Auth;

class Auth
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
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
            session_start();
        }
    }

    public static function user(?string $key = null)
    {
        self::start();

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            return null;
        }

        if ($key) {
            return $user[$key] ?? null;
        }

        return $user;
    }

    public static function check(): void
    {
        self::start();
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sessao expirada ou nao encontrada.']);
            exit;
        }
    }

    public static function login($usuario): void
    {
        self::start();
        session_regenerate_id(true);

        if (is_array($usuario)) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_tipo'] = $usuario['tipo'];
            $_SESSION['user'] = $usuario;
        } else {
            $_SESSION['user_id'] = $usuario;
        }
    }

    public static function logout(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function id(): ?int
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }
}
