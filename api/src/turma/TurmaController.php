<?php
namespace Turma;

use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;
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
     * GET /turmas - Lista turmas com DataTables (paginado, filtrado, buscavel)
     */
    public function index(): void {
        if (isset($_GET['simple']) && $_GET['simple'] === 'true') {
            $this->json($this->service->findAllSimple());
            return;
        }

        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = [
            'ativo' => $_GET['ativo'] ?? '',
        ];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    /**
     * GET /turmas/{id} - Obtem uma turma especifica
     */
    public function show(int $id): void {
        try {
            $turma = $this->service->findById($id);
            if (!$turma) {
                $this->error("Turma nao encontrada.", 404);
                return;
            }
            $this->json($turma);
        } catch (\Throwable $e) {
            error_log('[TurmaController::show] ' . $e->getMessage());
            $this->error("Erro ao obter turma.", 500);
        }
    }

    /**
     * GET /turmas/{id}/gerenciar - Obtem os dados agregados para gerenciamento
     */
    public function manage(int $id): void {
        try {
            $payload = $this->service->findManagementData(
                $id,
                $_GET['start'] ?? null,
                $_GET['end'] ?? null
            );
            if (!$payload) {
                $this->error("Turma nao encontrada.", 404);
                return;
            }
            $this->json($payload);
        } catch (\Throwable $e) {
            error_log('[TurmaController::manage] ' . $e->getMessage());
            $this->error("Erro ao obter dados de gerenciamento da turma.", 500);
        }
    }

    /**
     * POST /turmas/{id}/treinos - Agenda um treino para a turma
     */
    public function confirmTreino(int $id): void {
        try {
            $payload = $this->body();
            $treinoId = $this->service->confirmTreino($id, $payload);
            $this->json([
                'id' => $treinoId,
                'message' => 'Treino agendado com sucesso.'
            ], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[TurmaController::confirmTreino] ' . $e->getMessage());
            $this->error("Erro ao agendar treino da turma.", 500);
        }
    }

    /**
     * PUT /turmas/{id}/treinos/{treinoId}/presencas - Salva as presencas de um treino
     */
    public function savePresencas(int $id, int $treinoId): void {
        try {
            $payload = $this->body();
            $resumo = $this->service->savePresencas($id, $treinoId, $payload);
            $this->json([
                'message' => 'Presencas salvas com sucesso.',
                'resumo' => $resumo,
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TurmaController::savePresencas] ' . $e->getMessage());
            $this->error("Erro ao salvar presencas do treino.", 500);
        }
    }

    /**
     * PUT /turmas/{id}/treinos/{treinoId}/cancelar - Cancela uma ocorrencia de treino da turma
     */
    public function cancelTreino(int $id, int $treinoId): void {
        try {
            $this->service->cancelTreino($id, $treinoId);
            $this->json([
                'message' => 'Treino cancelado com sucesso.',
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TurmaController::cancelTreino] ' . $e->getMessage());
            $this->error("Erro ao cancelar treino da turma.", 500);
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
     * PUT /turmas/{id}/desativar - Desativa uma turma (soft delete)
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
