<?php
namespace Core\Http;

use Core\Auth\Auth;

abstract class Controller {
    protected function json($data = null, int $status = 200): void {
        \Core\Audit\Audit::responseId($data, $status);
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

    /**
     * Responde com erro a partir de uma exceção sem vazar detalhes internos.
     * Mensagens de domínio (validação/regra de negócio) são mantidas; falhas de
     * banco, erros de engine e mensagens que parecem SQL/caminhos viram genéricas.
     */
    protected function errorFrom(\Throwable $e, int $status = 400): void {
        $message = $e->getMessage();
        $internal = $e instanceof \PDOException
            || $e instanceof \Error
            || $e instanceof \ErrorException
            || preg_match('/SQLSTATE|PDO|mysql|stack trace|\.php|[A-Z]:\\\\|\/var\/|\/home\//i', $message) === 1;

        if ($internal) {
            error_log(sprintf('[%s] %s em %s:%d', static::class, $message, $e->getFile(), $e->getLine()));
            $status = $status >= 500 ? $status : 500;
            $message = 'Não foi possível concluir a operação. Tente novamente em instantes.';
        }

        $this->error($message, $status);
    }

    /** Exige token CSRF válido (endpoints públicos que alteram estado). */
    protected function requireCsrf(): void {
        \Core\Auth\Csrf::validate();
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
