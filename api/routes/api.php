<?php

/**
 * api/routes/api.php
 *
 * Definicao centralizada de todas as rotas da API
 */

use Core\Auth\AuthMiddleware;
use Core\Auth\RoleMiddleware;
use Core\Auth\PermissionMiddleware;
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
use Avaliacao\AvaliacaoFisicaController;
use Relatorio\RelatorioController;
use Financeiro\FinanceiroController;
use Docs\DocsController;

// Helpers de autorização
$auth   = [AuthMiddleware::class];
$admin  = [AuthMiddleware::class, RoleMiddleware::admin()];
$can    = fn(string ...$perms) => [AuthMiddleware::class, PermissionMiddleware::require(...$perms)];
$canAny = fn(string ...$perms) => [AuthMiddleware::class, PermissionMiddleware::any(...$perms)];
$canOrStudent = fn(string ...$perms) => [new class($perms) {
    public function __construct(private array $perms) {}
    public function handle(): bool {
        if (\Core\Auth\Auth::aluno()->isAuthenticated()) {
            return true;
        }

        if (\Core\Auth\Auth::admin()->isAuthenticated()) {
            if (\Core\Auth\Auth::isAdmin()) {
                return true;
            }
            foreach ($this->perms as $p) {
                if (\Core\Auth\Auth::can($p)) return true;
            }
        }

        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.'], JSON_UNESCAPED_UNICODE);
        return false;
    }
}];

// Documentação da API (Swagger UI / OpenAPI)
Router::get('/docs', [DocsController::class, 'ui']);
Router::get('/openapi.json', [DocsController::class, 'openapi']);
Router::get('/docs/openapi.json', [DocsController::class, 'openapi']);

// Relatórios e Agenda
Router::get('/relatorios/metricas', [RelatorioController::class, 'metricas'], $can('relatorios.visualizar'));
Router::get('/relatorios/gerar', [RelatorioController::class, 'gerar'], $can('relatorios.visualizar'));
Router::get('/relatorios/exportar', [RelatorioController::class, 'exportar'], $can('relatorios.exportar'));
Router::get('/agenda/treinos', [RelatorioController::class, 'agenda'], $canOrStudent('turmas.visualizar', 'treinos.visualizar', 'relatorios.visualizar'));

// Financeiro
Router::get('/financeiro/metricas', [FinanceiroController::class, 'metricas'], $canOrStudent('financeiro.visualizar'));
Router::get('/financeiro/exportar', [FinanceiroController::class, 'exportar'], $can('financeiro.visualizar'));
Router::get('/financeiro/servicos', [FinanceiroController::class, 'servicos'], $can('financeiro.visualizar'));
Router::get('/financeiro/servicos/{id}', [FinanceiroController::class, 'servico'], $can('financeiro.visualizar'));
Router::put('/financeiro/servicos/{id}/status', [FinanceiroController::class, 'statusServico'], $can('financeiro.gerenciar'));
Router::get('/financeiro/planos/{id}', [FinanceiroController::class, 'plano'], $can('financeiro.visualizar'));
Router::put('/financeiro/planos/{id}', [FinanceiroController::class, 'atualizarPlano'], $can('financeiro.gerenciar'));
Router::put('/financeiro/planos/{id}/status', [FinanceiroController::class, 'statusPlano'], $can('financeiro.gerenciar'));
Router::put('/financeiro/contratos/{id}/status', [FinanceiroController::class, 'statusContrato'], $can('financeiro.gerenciar'));
Router::post('/financeiro/servicos', [FinanceiroController::class, 'criarServico'], $can('financeiro.gerenciar'));
Router::put('/financeiro/servicos/{id}', [FinanceiroController::class, 'atualizarServico'], $can('financeiro.gerenciar'));

Router::get('/financeiro/planos', [FinanceiroController::class, 'planos'], $can('financeiro.visualizar'));
Router::post('/financeiro/planos', [FinanceiroController::class, 'criarPlano'], $can('financeiro.gerenciar'));

Router::get('/financeiro/contratos', [FinanceiroController::class, 'contratos'], $canOrStudent('financeiro.visualizar'));
Router::get('/financeiro/contratos/{id}', [FinanceiroController::class, 'contrato'], $canOrStudent('financeiro.visualizar'));
Router::post('/financeiro/contratos', [FinanceiroController::class, 'criarContrato'], $can('financeiro.gerenciar'));
Router::post('/financeiro/contratos/{id}/cobrancas', [FinanceiroController::class, 'gerarCobranca'], $can('financeiro.gerenciar'));

Router::get('/financeiro/cobrancas', [FinanceiroController::class, 'cobrancas'], $canOrStudent('financeiro.visualizar'));
Router::get('/financeiro/cobrancas/{id}', [FinanceiroController::class, 'cobranca'], $canOrStudent('financeiro.visualizar'));
Router::get('/financeiro/cobrancas/{id}/encargos', [FinanceiroController::class, 'calcularEncargos'], $canOrStudent('financeiro.visualizar'));
Router::get('/financeiro/cobrancas/{id}/pagamentos/{pagamento_id}/recibo', [FinanceiroController::class, 'recibo'], $canOrStudent('financeiro.visualizar'));
Router::post('/financeiro/cobrancas/atualizar-vencidas', [FinanceiroController::class, 'atualizarVencidas'], $can('financeiro.gerenciar'));
Router::post('/financeiro/cobrancas/{id}/pagamentos', [FinanceiroController::class, 'registrarPagamento'], $can('financeiro.registrar_pagamento'));
Router::post('/financeiro/cobrancas/gerar-automaticas', [FinanceiroController::class, 'gerarAutomaticas'], $can('financeiro.gerenciar'));
Router::post('/financeiro/cobrancas/{id}/cancelar', [FinanceiroController::class, 'cancelarCobranca'], $can('financeiro.gerenciar'));
Router::post('/financeiro/cobrancas/{id}/pagamentos/{pagamento_id}/estornar', [FinanceiroController::class, 'estornarPagamento'], $can('financeiro.registrar_pagamento'));

// Autenticação
Router::post('/auth/login', [AuthController::class, 'login']);
Router::post('/auth/logout', [AuthController::class, 'logout']);
Router::post('/auth/recuperar-senha', [AuthController::class, 'requestPasswordReset']);
Router::post('/auth/redefinir-senha', [AuthController::class, 'resetPassword']);
Router::post('/auth/ativar-conta', [AuthController::class, 'activateAccount']);
Router::post('/auth/reenviar-ativacao', [AuthController::class, 'resendActivationLink'], $auth);

// Usuários Gerais
Router::get('/usuarios', [UsuarioController::class, 'index'], $can('usuarios.gerenciar'));
Router::get('/usuarios/me', [UsuarioController::class, 'me'], $auth);
Router::get('/usuarios/{id}', [UsuarioController::class, 'show'], $can('usuarios.gerenciar'));
Router::put('/usuarios/{id}', [UsuarioController::class, 'update'], $canOrStudent('usuarios.gerenciar'));
Router::put('/usuarios/{id}/desativar', [UsuarioController::class, 'deactivate'], $can('usuarios.gerenciar'));
Router::put('/usuarios/{id}/reativar', [UsuarioController::class, 'reactivate'], $can('usuarios.gerenciar'));

// Alunos
Router::get('/alunos', [AlunoController::class, 'index'], $canAny('alunos.visualizar', 'financeiro.gerenciar', 'financeiro.visualizar'));
Router::get('/alunos/{id}', [AlunoController::class, 'show'], $canOrStudent('alunos.visualizar'));
Router::get('/alunos/{id}/avaliacoes', [AvaliacaoFisicaController::class, 'indexByAluno'], $canOrStudent('avaliacoes.visualizar'));
Router::post('/alunos/{id}/avaliacoes', [AvaliacaoFisicaController::class, 'storeByAluno'], $can('avaliacoes.criar'));
Router::post('/alunos', [AlunoController::class, 'store'], $can('alunos.criar'));
Router::put('/alunos/{id}', [AlunoController::class, 'update'], $can('alunos.editar'));
Router::put('/alunos/{id}/desativar', [AlunoController::class, 'deactivate'], $can('alunos.desativar'));
Router::put('/alunos/{id}/reativar', [AlunoController::class, 'reactivate'], $can('alunos.desativar'));

// Avaliações
Router::get('/avaliacoes/{id}', [AvaliacaoFisicaController::class, 'show'], $canOrStudent('avaliacoes.visualizar'));
Router::put('/avaliacoes/{id}', [AvaliacaoFisicaController::class, 'update'], $can('avaliacoes.editar'));

// Anamnese
Router::get('/anamnese/formularios', [AnamneseController::class, 'listar'], $canAny('anamnese.visualizar', 'anamnese.preencher'));
Router::get('/anamnese/formularios/{id}', [AnamneseController::class, 'obterFormulario'], $canAny('anamnese.visualizar', 'anamnese.preencher'));
Router::get('/anamnese/formularios/{id}/perguntas', [AnamneseController::class, 'index'], $canAny('anamnese.visualizar', 'anamnese.preencher'));
Router::get('/anamnese/respostas/{aluno_id}', [AnamneseController::class, 'show'], $canOrStudent('anamnese.visualizar'));
Router::post('/anamnese', [AnamneseController::class, 'store'], $can('anamnese.preencher'));

// Permissões e Cargos
Router::get('/permissoes', [CargoController::class, 'permissoes'], $canAny('cargos.visualizar', 'cargos.gerenciar'));
Router::get('/cargos', [CargoController::class, 'index'], $canAny('cargos.visualizar', 'funcionarios.visualizar', 'funcionarios.gerenciar'));
Router::get('/cargos/{id}', [CargoController::class, 'show'], $canAny('cargos.visualizar', 'cargos.gerenciar'));
Router::post('/cargos', [CargoController::class, 'store'], $can('cargos.gerenciar'));
Router::put('/cargos/{id}', [CargoController::class, 'update'], $can('cargos.gerenciar'));
Router::put('/cargos/{id}/desativar', [CargoController::class, 'deactivate'], $can('cargos.gerenciar'));
Router::put('/cargos/{id}/reativar', [CargoController::class, 'reactivate'], $can('cargos.gerenciar'));

// Modalidades
Router::get('/modalidades', [ModalidadeController::class, 'index'], $canAny('modalidades.visualizar', 'treinos.visualizar', 'turmas.visualizar'));
Router::get('/modalidades/{id}', [ModalidadeController::class, 'show'], $can('modalidades.visualizar'));
Router::post('/modalidades', [ModalidadeController::class, 'store'], $can('modalidades.gerenciar'));
Router::put('/modalidades/{id}', [ModalidadeController::class, 'update'], $can('modalidades.gerenciar'));
Router::put('/modalidades/{id}/desativar', [ModalidadeController::class, 'deactivate'], $can('modalidades.gerenciar'));
Router::put('/modalidades/{id}/reativar', [ModalidadeController::class, 'reactivate'], $can('modalidades.gerenciar'));

// Funcionários
Router::get('/funcionarios', [FuncionarioController::class, 'index'], $canAny('funcionarios.visualizar', 'turmas.gerenciar', 'turmas.criar'));
Router::get('/funcionarios/{id}', [FuncionarioController::class, 'show'], $can('funcionarios.visualizar'));
Router::post('/funcionarios', [FuncionarioController::class, 'store'], $can('funcionarios.gerenciar'));
Router::put('/funcionarios/{id}', [FuncionarioController::class, 'update'], $can('funcionarios.gerenciar'));
Router::put('/funcionarios/{id}/desativar', [FuncionarioController::class, 'deactivate'], $can('funcionarios.gerenciar'));
Router::put('/funcionarios/{id}/reativar', [FuncionarioController::class, 'reactivate'], $can('funcionarios.gerenciar'));

// Turmas
Router::get('/turmas', [TurmaController::class, 'index'], $canOrStudent('turmas.visualizar'));
Router::get('/turmas/{id}/gerenciar', [TurmaController::class, 'manage'], $can('turmas.gerenciar'));
Router::post('/turmas/{id}/treinos', [TurmaController::class, 'confirmTreino'], $can('turmas.gerenciar'));
Router::put('/turmas/{id}/treinos/{treino_id}/cancelar', [TurmaController::class, 'cancelTreino'], $can('turmas.gerenciar'));
Router::put('/turmas/{id}/treinos/{treino_id}/presencas', [TurmaController::class, 'savePresencas'], $can('turmas.gerenciar'));
Router::get('/turmas/{id}', [TurmaController::class, 'show'], $canOrStudent('turmas.visualizar'));
Router::post('/turmas', [TurmaController::class, 'store'], $can('turmas.criar'));
Router::put('/turmas/{id}', [TurmaController::class, 'update'], $can('turmas.editar'));
Router::put('/turmas/{id}/desativar', [TurmaController::class, 'deactivate'], $can('turmas.desativar'));
Router::put('/turmas/{id}/reativar', [TurmaController::class, 'reactivate'], $can('turmas.desativar'));

// Locais
Router::get('/locais', [LocalController::class, 'index'], $canAny('locais.visualizar', 'turmas.visualizar', 'turmas.criar'));
Router::get('/locais/{id}', [LocalController::class, 'show'], $can('locais.visualizar'));
Router::post('/locais', [LocalController::class, 'store'], $can('locais.gerenciar'));
Router::put('/locais/{id}', [LocalController::class, 'update'], $can('locais.gerenciar'));
Router::put('/locais/{id}/desativar', [LocalController::class, 'deactivate'], $can('locais.gerenciar'));
Router::put('/locais/{id}/reativar', [LocalController::class, 'reactivate'], $can('locais.gerenciar'));

// Treinos
Router::get('/treinos', [TreinoController::class, 'index'], $can('treinos.visualizar'));
Router::get('/treinos/{id}', [TreinoController::class, 'show'], $can('treinos.visualizar'));
Router::post('/treinos', [TreinoController::class, 'store'], $can('treinos.criar'));
Router::put('/treinos/{id}', [TreinoController::class, 'update'], $can('treinos.editar'));
Router::put('/treinos/{id}/reativar', [TreinoController::class, 'reactivate'], $can('treinos.desativar'));
Router::delete('/treinos/{id}', [TreinoController::class, 'cancelar'], $can('treinos.desativar'));
