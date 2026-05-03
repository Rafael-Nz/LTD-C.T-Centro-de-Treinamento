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
use Anamnese\AnamneseController;

// ========================================
// ROTAS DE ALUNOS
// ========================================
Router::get('/alunos', [AlunoController::class, 'index']);
Router::get('/alunos/{id}', [AlunoController::class, 'show']);
Router::post('/alunos', [AlunoController::class, 'store']);
Router::put('/alunos/{id}', [AlunoController::class, 'update']);
Router::put('/alunos/{id}/desativar', [AlunoController::class, 'deactivate']);
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate']);

// ========================================
// ROTAS DE CARGOS
// ========================================
Router::get('/cargos', [CargoController::class, 'index']);
Router::get('/cargos/{id}', [CargoController::class, 'show']);
Router::post('/cargos', [CargoController::class, 'store']);
Router::put('/cargos/{id}', [CargoController::class, 'update']);
Router::put('/cargos/{id}/desativar', [CargoController::class, 'deactivate']);
Router::put('/cargos/{id}/reativar', [CargoController::class, 'reactivate']);

// ========================================
// ROTAS DE FUNCIONÁRIOS
// ========================================
Router::get('/funcionarios', [FuncionarioController::class, 'index']);
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show']);
Router::post('/funcionarios', [FuncionarioController::class, 'store']);
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update']);
Router::put('/funcionarios/{id}/desativar', [FuncionarioController::class, 'deactivate']);
Router::put('/funcionarios/{id}/reativar', [FuncionarioController::class, 'reactivate']);
// ========================================
// ROTAS DE USUÁRIOS
// ========================================
Router::get('/usuarios', [UsuarioController::class, 'index']);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show']);
Router::put('/usuarios/{id}', [UsuarioController::class, 'update']);
Router::put('/usuarios/{id}/desativar', [UsuarioController::class, 'deactivate']);
Router::put('/usuarios/{id}/reativar', [UsuarioController::class, 'reactivate']);
