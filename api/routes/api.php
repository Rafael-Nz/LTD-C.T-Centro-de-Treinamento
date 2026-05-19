<?php
/**
 * api/routes/api.php
 *
 * Definicao centralizada de todas as rotas da API
 */

use Core\Auth\AuthMiddleware;
use Core\Http\Router;
use Auth\AuthController;
use Aluno\AlunoController;
use Cargo\CargoController;
use Funcionario\FuncionarioController;
use Usuario\UsuarioController;
use Anamnese\AnamneseController;
use Local\LocalController;
use Modalidade\ModalidadeController;
use Treino\TreinoController;
use Turma\TurmaController;

$auth = [AuthMiddleware::class];

Router::post('/auth/login', [AuthController::class, 'login']);
Router::post('/auth/logout', [AuthController::class, 'logout']);
Router::post('/auth/recuperar-senha', [AuthController::class, 'requestPasswordReset']);
Router::post('/auth/redefinir-senha', [AuthController::class, 'resetPassword']);

Router::get('/usuarios', [UsuarioController::class, 'index'], $auth);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show'], $auth);
Router::put('/usuarios/{id}', [UsuarioController::class, 'update'], $auth);
Router::put('/usuarios/{id}/desativar', [UsuarioController::class, 'deactivate'], $auth);
Router::put('/usuarios/{id}/reativar', [UsuarioController::class, 'reactivate'], $auth);

Router::get('/alunos', [AlunoController::class, 'index'], $auth);
Router::get('/alunos/{id}', [AlunoController::class, 'show'], $auth);
Router::post('/alunos', [AlunoController::class, 'store'], $auth);
Router::put('/alunos/{id}', [AlunoController::class, 'update'], $auth);
Router::put('/alunos/{id}/desativar', [AlunoController::class, 'deactivate'], $auth);
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate'], $auth);

Router::get('/anamnese/formularios', [AnamneseController::class, 'listar'], $auth);
Router::get('/anamnese/formularios/{id}', [AnamneseController::class, 'obterFormulario'], $auth);
Router::get('/anamnese/formularios/{id}/perguntas', [AnamneseController::class, 'index'], $auth);
Router::get('/anamnese/respostas/{aluno_id}', [AnamneseController::class, 'show'], $auth);
Router::post('/anamnese', [AnamneseController::class, 'store'], $auth);

Router::get('/cargos', [CargoController::class, 'index'], $auth);
Router::get('/cargos/{id}', [CargoController::class, 'show'], $auth);
Router::post('/cargos', [CargoController::class, 'store'], $auth);
Router::put('/cargos/{id}', [CargoController::class, 'update'], $auth);
Router::put('/cargos/{id}/desativar', [CargoController::class, 'deactivate'], $auth);
Router::put('/cargos/{id}/reativar', [CargoController::class, 'reactivate'], $auth);

Router::get('/modalidades', [ModalidadeController::class, 'index'], $auth);
Router::get('/modalidades/{id}', [ModalidadeController::class, 'show'], $auth);
Router::post('/modalidades', [ModalidadeController::class, 'store'], $auth);
Router::put('/modalidades/{id}', [ModalidadeController::class, 'update'], $auth);
Router::put('/modalidades/{id}/desativar', [ModalidadeController::class, 'deactivate'], $auth);
Router::put('/modalidades/{id}/reativar', [ModalidadeController::class, 'reactivate'], $auth);

Router::get('/funcionarios', [FuncionarioController::class, 'index'], $auth);
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show'], $auth);
Router::post('/funcionarios', [FuncionarioController::class, 'store'], $auth);
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update'], $auth);
Router::put('/funcionarios/{id}/desativar', [FuncionarioController::class, 'deactivate'], $auth);
Router::put('/funcionarios/{id}/reativar', [FuncionarioController::class, 'reactivate'], $auth);

Router::get('/turmas', [TurmaController::class, 'index'], $auth);
Router::get('/turmas/{id}/gerenciar', [TurmaController::class, 'manage'], $auth);
Router::post('/turmas/{id}/treinos', [TurmaController::class, 'confirmTreino'], $auth);
Router::put('/turmas/{id}/treinos/{treino_id}/presencas', [TurmaController::class, 'savePresencas'], $auth);
Router::put('/turmas/{id}/treinos/{treino_id}/cancelar', [TurmaController::class, 'cancelTreino'], $auth);
Router::get('/turmas/{id}', [TurmaController::class, 'show'], $auth);
Router::post('/turmas', [TurmaController::class, 'store'], $auth);
Router::put('/turmas/{id}', [TurmaController::class, 'update'], $auth);
Router::put('/turmas/{id}/desativar', [TurmaController::class, 'deactivate'], $auth);
Router::put('/turmas/{id}/reativar', [TurmaController::class, 'reactivate'], $auth);

Router::get('/locais', [LocalController::class, 'index'], $auth);
Router::get('/locais/{id}', [LocalController::class, 'show'], $auth);
Router::post('/locais', [LocalController::class, 'store'], $auth);
Router::put('/locais/{id}', [LocalController::class, 'update'], $auth);
Router::put('/locais/{id}/desativar', [LocalController::class, 'deactivate'], $auth);
Router::put('/locais/{id}/reativar', [LocalController::class, 'reactivate'], $auth);

Router::get('/treinos', [TreinoController::class, 'index'], $auth);
Router::get('/treinos/{id}', [TreinoController::class, 'show'], $auth);
Router::post('/treinos', [TreinoController::class, 'store'], $auth);
Router::put('/treinos/{id}', [TreinoController::class, 'update'], $auth);
Router::put('/treinos/{id}/reativar', [TreinoController::class, 'reactivate'], $auth);
Router::delete('/treinos/{id}', [TreinoController::class, 'cancelar'], $auth);
