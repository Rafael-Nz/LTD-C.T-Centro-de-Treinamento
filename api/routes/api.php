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
use Local\LocalController;
use Treino\TreinoController;

// Atalho para o middleware de proteção
$auth = [AuthMiddleware::class];

// ROTAS DE AUTENTICAÇÃO
Router::post('/auth/login', [AuthController::class, 'login']);
Router::post('/auth/logout', [AuthController::class, 'logout']);
Router::post('/auth/recuperar-senha', [AuthController::class, 'requestPasswordReset']);
Router::post('/auth/redefinir-senha', [AuthController::class, 'resetPassword']);

// ROTAS DE USUÁRIOS
Router::get('/usuarios', [UsuarioController::class, 'index'], $auth);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show'], $auth);
Router::put('/usuarios/{id}', [UsuarioController::class, 'update'], $auth);
Router::put('/usuarios/{id}/desativar', [UsuarioController::class, 'deactivate'], $auth);
Router::put('/usuarios/{id}/reativar', [UsuarioController::class, 'reactivate'], $auth);

// ROTAS DE ALUNOS
Router::get('/alunos', [AlunoController::class, 'index'], $auth);
Router::get('/alunos/{id}', [AlunoController::class, 'show'], $auth);
Router::post('/alunos', [AlunoController::class, 'store'], $auth);
Router::put('/alunos/{id}', [AlunoController::class, 'update'], $auth);
Router::put('/alunos/{id}/desativar', [AlunoController::class, 'deactivate'], $auth);
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate'], $auth);
// ROTAS DE ANAMNESE
Router::get('/anamnese/formularios', [AnamneseController::class, 'listar'], $auth);
Router::get('/anamnese/formularios/{id}', [AnamneseController::class, 'obterFormulario'], $auth);
Router::get('/anamnese/formularios/{id}/perguntas', [AnamneseController::class, 'index'], $auth);
Router::get('/anamnese/respostas/{alunoId}', [AnamneseController::class, 'show'], $auth);
Router::post('/anamnese', [AnamneseController::class, 'store'], $auth);

// ROTAS DE CARGOS
Router::get('/cargos', [CargoController::class, 'index'], $auth);
Router::get('/cargos/{id}', [CargoController::class, 'show'], $auth);
Router::post('/cargos', [CargoController::class, 'store'], $auth);
Router::put('/cargos/{id}', [CargoController::class, 'update'], $auth);
Router::put('/cargos/{id}/desativar', [CargoController::class, 'deactivate'], $auth);
Router::put('/cargos/{id}/reativar', [CargoController::class, 'reactivate'], $auth);

// ROTAS DE FUNCIONÁRIOS
Router::get('/funcionarios', [FuncionarioController::class, 'index'], $auth);
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show'], $auth);
Router::post('/funcionarios', [FuncionarioController::class, 'store'], $auth);
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update'], $auth);
Router::put('/funcionarios/{id}/desativar', [FuncionarioController::class, 'deactivate'], $auth);
Router::put('/funcionarios/{id}/reativar', [FuncionarioController::class, 'reactivate'], $auth);

// ROTAS DE LOCAL DE TREINO -- caio
Router::get('/local', [LocalController::class, 'index'], $auth);
Router::get('/local/{id}', [LocalController::class, 'show'], $auth);
Router::post('/local', [LocalController::class, 'store'], $auth);
Router::put('/local/{id}', [LocalController::class, 'update'], $auth);
Router::delete('/local/{id}/desativar', [LocalController::class, 'deactivate'], $auth);
Router::put('/local/{id}/reativar',[LocalController::class, 'reactivate'], $auth);

// ROTAS DE TREINOS (agenda) -- caio
Router::get('/treinos', [TreinoController::class, 'index'], $auth);
Router::get('/treinos/{id}', [TreinoController::class, 'show'], $auth);
Router::post('/treinos', [TreinoController::class, 'store'], $auth);
Router::put('/treinos/{id}', [TreinoController::class, 'update'], $auth);
Router::delete('/treinos/{id}', [TreinoController::class, 'cancelar'], $auth);