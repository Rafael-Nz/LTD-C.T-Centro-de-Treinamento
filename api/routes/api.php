<?php
/**
 * api/routes/api.php
 * 
 * Definição centralizada de todas as rotas da API
 */

use Core\Router;
use Aluno\AlunoController;
use Cargo\CargoController;
use Funcionario\FuncionarioController;
use Usuario\UsuarioController;

// ========================================
// ROTAS DE ALUNOS
// ========================================
Router::get('/alunos', [AlunoController::class, 'index']);
Router::get('/alunos/{id}', [AlunoController::class, 'show']);
Router::post('/alunos', [AlunoController::class, 'store']);
Router::put('/alunos/{id}', [AlunoController::class, 'update']);
Router::delete('/alunos/{id}', [AlunoController::class, 'destroy']);
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate']);

// ========================================
// ROTAS DE CARGOS
// ========================================
Router::get('/cargos', [CargoController::class, 'index']);
Router::get('/cargos/{id}', [CargoController::class, 'show']);
Router::post('/cargos', [CargoController::class, 'store']);
Router::put('/cargos/{id}', [CargoController::class, 'update']);
Router::delete('/cargos/{id}', [CargoController::class, 'destroy']);

// ========================================
// ROTAS DE FUNCIONÁRIOS
// ========================================
Router::get('/funcionarios', [FuncionarioController::class, 'index']);
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show']);
Router::post('/funcionarios', [FuncionarioController::class, 'store']);
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update']);
Router::delete('/funcionarios/{id}', [FuncionarioController::class, 'destroy']);

// ========================================
// ROTAS DE USUÁRIOS
// ========================================
Router::get('/usuarios', [UsuarioController::class, 'index']);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show']);
Router::post('/usuarios', [UsuarioController::class, 'store']);
Router::put('/usuarios/{id}', [UsuarioController::class, 'update']);
Router::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);
