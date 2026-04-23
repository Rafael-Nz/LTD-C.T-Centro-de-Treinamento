<?php
/**
 * api/v1/turma/index.php
 *
 * Rotas disponíveis:
 *   GET    /v1/turma/              → lista todas as turmas
 *   GET    /v1/turma/:id           → detalhe de uma turma
 *   GET    /v1/turma/:id/alunos    → lista alunos da turma
 *   GET    /v1/turma/:id/treinos   → lista treinos da turma
 *   POST   /v1/turma/              → cadastra nova turma
 *   PUT    /v1/turma/:id           → atualiza turma existente
 *   DELETE /v1/turma/:id           → deleta turma
 */

require_once __DIR__ . '/../../bootstrap.php';

use Turma\TurmaController;

$controller = new TurmaController();
$method     = $_SERVER['REQUEST_METHOD'];

// Extrai a URI e remove query string
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove o prefixo /api/v1/turma
$prefix = '/api/v1/turma';
if (strpos($uri, $prefix) === 0) {
    $path = substr($uri, strlen($prefix));
} else {
    $path = $uri;
}

// Remove trailing slash e limpa a path
$path = rtrim($path, '/');

// Divide a path em segmentos
$segments = array_filter(explode('/', $path), 'strlen');

match ($method) {
    'GET' => handleGet($controller, $segments),
    'POST' => $controller->store(),
    'PUT' => handlePut($controller, $segments),
    'DELETE' => handleDelete($controller, $segments),
    default => sendError('Método não permitido.', 405)
};

function handleGet($controller, $segments) {
    if (empty($segments)) {
        // GET /v1/turma/
        $controller->index();
    } elseif (count($segments) === 1) {
        // GET /v1/turma/:id
        $controller->index([$segments[0]]);
    } elseif (count($segments) === 2) {
        // GET /v1/turma/:id/:action
        $action = $segments[1];
        $id = $segments[0];

        match ($action) {
            'alunos' => $controller->alunos([$id]),
            'treinos' => $controller->treinos([$id]),
            default => sendError('Rota não encontrada.', 404)
        };
    } else {
        sendError('Rota não encontrada.', 404);
    }
}

function handlePut($controller, $segments) {
    if (empty($segments)) {
        sendError('ID obrigatório.', 400);
    }

    if (count($segments) === 1) {
        // PUT /v1/turma/:id
        $controller->update([$segments[0]]);
    } else {
        sendError('Rota não encontrada.', 404);
    }
}

function handleDelete($controller, $segments) {
    if (empty($segments)) {
        sendError('ID obrigatório.', 400);
    }

    if (count($segments) === 1) {
        // DELETE /v1/turma/:id
        $controller->delete([$segments[0]]);
    } else {
        sendError('Rota não encontrada.', 404);
    }
}

function sendError(string $message, int $status = 400): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
