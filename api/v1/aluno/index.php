<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * api/v1/alunos/index.php
 *
 * Rotas disponíveis:
 *   GET    /v1/alunos/          → lista todos os alunos
 *   GET    /v1/alunos/?id=N     → detalhe de um aluno
 *   POST   /v1/alunos/          → cadastra novo aluno
 *   PUT    /v1/alunos/?id=N     → atualiza aluno existente
 *   DELETE /v1/alunos/?id=N     → desativa aluno (soft delete)
 */

require_once __DIR__ . '/../../bootstrap.php';

use Aluno\AlunoController;

$controller = new AlunoController();
$method     = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'GET'    => $controller->index(),
    'POST'   => $controller->store(),
    'PUT'    => $controller->update(),
    'DELETE' => $controller->destroy(),
    default  => (function () {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    })()
};