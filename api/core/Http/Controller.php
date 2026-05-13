<?php
namespace Core\Http;

use Core\Auth\Auth;

abstract class Controller {
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

    protected function error(string $message, int $status = 400): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $message
        ]);

        exit;
    }

    protected function only(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->error("Metodo nao permitido", 405);
        }
    }

    protected function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function auth(): void {
        Auth::check();
    }
}
