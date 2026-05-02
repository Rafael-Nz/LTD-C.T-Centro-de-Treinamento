<?php
namespace Core;

abstract class Controller {

    /**
     * Resposta JSON padrão
     */
    protected function json($data = null, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => $status < 400,
            'data' => $data
        ]);

        exit;
    }

    protected function datatable(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($payload);
        exit;
    }

    /**
     * Resposta de erro padronizada
     */
    protected function error(string $message, int $status = 400): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $message
        ]);

        exit;
    }

    /**
     * Garante método HTTP
     */
    protected function only(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->error("Método não permitido", 405);
        }
    }

    /**
     * Retorna JSON enviado no body
     */
    protected function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    /**
     * Input seguro (GET ou POST)
     */
    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Verificação simples de autenticação
     */
    protected function auth(): void {
        Auth::check();
    }
}