<?php
/**
 * api/routes/api.php
 * 
 * Definição centralizada de todas as rotas da API
 */

use Core\Router;
use Core\AuthMiddleware;
use Auth\AuthController;
use Aluno\AlunoController;
use Cargo\CargoController;
use Funcionario\FuncionarioController;
use Usuario\UsuarioController;
use Anamnese\AnamneseController;

// Atalho para o middleware de proteção
$auth = [AuthMiddleware::class];

// ROTAS DE AUTENTICAÇÃO

Router::post('/auth/login', [AuthController::class, 'login']);
Router::post('/auth/logout', [AuthController::class, 'logout']);
Router::post('/auth/recuperar-senha', [AuthController::class, 'requestPasswordReset']);
Router::post('/auth/redefinir-senha', [AuthController::class, 'resetPassword']);

// ========================================
// ROTAS DE ALUNOS
// ========================================
Router::get('/alunos', [AlunoController::class, 'index'], $auth);
Router::get('/alunos/{id}', [AlunoController::class, 'show'], $auth);
Router::post('/alunos', [AlunoController::class, 'store'], $auth);
Router::put('/alunos/{id}', [AlunoController::class, 'update'], $auth);
Router::put('/alunos/{id}/desativar', [AlunoController::class, 'deactivate'], $auth);
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate'], $auth);

// ========================================
// ROTAS DE CARGOS
// ========================================
Router::get('/cargos', [CargoController::class, 'index'], $auth);
Router::get('/cargos/{id}', [CargoController::class, 'show'], $auth);
Router::post('/cargos', [CargoController::class, 'store'], $auth);
Router::put('/cargos/{id}', [CargoController::class, 'update'], $auth);
Router::put('/cargos/{id}/desativar', [CargoController::class, 'deactivate'], $auth);
Router::put('/cargos/{id}/reativar', [CargoController::class, 'reactivate'], $auth);

// ========================================
// ROTAS DE FUNCIONÁRIOS
// ========================================
Router::get('/funcionarios', [FuncionarioController::class, 'index'], $auth);
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show'], $auth);
Router::post('/funcionarios', [FuncionarioController::class, 'store'], $auth);
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update'], $auth);
Router::put('/funcionarios/{id}/desativar', [FuncionarioController::class, 'deactivate'], $auth);
Router::put('/funcionarios/{id}/reativar', [FuncionarioController::class, 'reactivate'], $auth);
// ========================================
// ROTAS DE USUÁRIOS
// ========================================
Router::get('/usuarios', [UsuarioController::class, 'index'], $auth);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show'], $auth);
Router::put('/usuarios/{id}', [UsuarioController::class, 'update'], $auth);
Router::put('/usuarios/{id}/desativar', [UsuarioController::class, 'deactivate'], $auth);
Router::put('/usuarios/{id}/reativar', [UsuarioController::class, 'reactivate'], $auth);
