<?php
namespace Turma;

use Core\Controller;
use Core\DataTablesResponseTrait;
use Turma\DTO\TurmaDTO;

class TurmaController extends Controller {
    use DataTablesResponseTrait;

    private TurmaService $service;
    private TurmaRepository $repo;

    public function __construct() {
        $this->service = new TurmaService();
        $this->repo = new TurmaRepository();
    }

    /**
     * GET /turmas - Lista turmas com DataTables (paginado, filtrado, buscável)
     */
    public function index(): void {
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = [
            'turno'        => $_GET['turno']         ?? '',
            'ativo'        => $_GET['ativo']         ?? '',
            'modalidade_id' => $_GET['modalidade_id'] ?? '',
        ];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    /**
     * GET /turmas/{id} - Obtém uma turma específica
     */
    public function show(int $id): void {
        try {
            $turma = $this->service->findById($id);
            if (!$turma) {
                $this->error("Turma não encontrada.", 404);
                return;
            }
            $this->json($turma);
        } catch (\Throwable $e) {
            error_log('[TurmaController::show] ' . $e->getMessage());
            $this->error("Erro ao obter turma.", 500);
        }
    }

    /**
     * POST /turmas - Cria uma nova turma
     */
    public function store(): void {
        try {
            $dto = TurmaDTO::fromArray($this->body());
            $id  = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Turma criada com sucesso.'], 201);
        } catch (\Exception $e) {
            error_log('[TurmaController::store] ' . $e->getMessage());
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[TurmaController::store] ' . $e->getMessage());
            $this->error("Erro interno ao criar turma.", 500);
        }
    }

    /**
     * PUT /turmas/{id} - Atualiza uma turma
     */
    public function update(int $id): void {
        try {
            $dto = TurmaDTO::fromArray($this->body());
            $this->service->update($id, $dto);
            $this->json(['message' => 'Turma atualizada com sucesso.']);
        } catch (\Exception $e) {
            error_log('[TurmaController::update] ' . $e->getMessage());
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[TurmaController::update] ' . $e->getMessage());
            $this->error("Erro interno ao atualizar turma.", 500);
        }
    }

    /**
     * DELETE /turmas/{id} - Desativa uma turma (soft delete)
     */
    public function deactivate(int $id): void {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Turma desativada com sucesso.']);
        } catch (\Exception $e) {
            error_log('[TurmaController::deactivate] ' . $e->getMessage());
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TurmaController::deactivate] ' . $e->getMessage());
            $this->error("Erro interno ao desativar turma.", 500);
        }
    }

    /**
     * PUT /turmas/{id}/reativar - Reativa uma turma
     */
    public function reactivate(int $id): void {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Turma reativada com sucesso.']);
        } catch (\Exception $e) {
            error_log('[TurmaController::reactivate] ' . $e->getMessage());
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TurmaController::reactivate] ' . $e->getMessage());
            $this->error("Erro interno ao reativar turma.", 500);
        }
    }
}
