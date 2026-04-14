<?php
require_once '../bootstrap.php';

$controller = new AuthController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $controller->login();
        break;

    case 'logout':
        $controller->logout();
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Rota inválida']);
}