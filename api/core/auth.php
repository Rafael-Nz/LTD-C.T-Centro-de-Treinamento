<?php
namespace Core;

class Auth {
    private static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Recupera um dado do usuário logado na sessão
     */
    public static function user(?string $key = null) {
        self::start();

        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            return null;
        }

        // Se pedir uma chave específica (ex: 'id'), retorna só ela
        if ($key) {
            return $user[$key] ?? null;
        }

        return $user;
    }
    
    /**
     * Bloqueia o acesso se não houver sessão
     */
    public static function check(): void {
        self::start();
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sessão expirada ou não encontrada.']);
            exit;
        }
    }

    public static function login($usuario): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (is_array($usuario)) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_tipo'] = $usuario['tipo'];
            $_SESSION['user'] = $usuario;
        } else {
            $_SESSION['user_id'] = $usuario;
        }
    }

    public static function logout(): void {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function id(): ?int {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }
    
}