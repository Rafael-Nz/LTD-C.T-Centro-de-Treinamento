<?php

namespace Core\Auth;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const COOKIE_NAME = 'CTT_CSRF_TOKEN';

    public static function token(): string
    {
        Auth::start();

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        self::setCookie($_SESSION[self::SESSION_KEY]);
        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(): void
    {
        $sessionToken = self::token();
        $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if ($requestToken === '' || !hash_equals($sessionToken, $requestToken)) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Token CSRF ausente ou invalido.']);
            exit;
        }
    }

    private static function setCookie(string $token): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie(self::COOKIE_NAME, $token, [
            'expires' => 0,
            'path' => '/ctt',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}
