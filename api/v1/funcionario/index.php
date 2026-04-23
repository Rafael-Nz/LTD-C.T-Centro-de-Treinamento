<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * api/v1/funcionario/index.php
 *
 * Rotas disponíveis:
 *   GET    /v1/funcionario/          → lista todos os funcionários
 *   GET    /v1/funcionario/?id=N     → detalhe de um funcionário
 *   POST   /v1/funcionario/          → cadastra novo funcionário
 *   PUT    /v1/funcionario/?id=N     → atualiza funcionário existente
 *   DELETE /v1/funcionario/?id=N     → desativa funcionário (soft delete)
 */

require_once __DIR__ . '/../../bootstrap.php';

use Funcionario\FuncionarioController;

$controller = new FuncionarioController();
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
