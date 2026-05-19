<?php
namespace Treino;

use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;
use Treino\DTO\TreinoDTO;

class TreinoController extends Controller {
    use DataTablesResponseTrait;

    private TreinoRepository $repo;
    private TreinoService $service;

    public function __construct() {
        $this->repo = new TreinoRepository();
        $this->service = new TreinoService();
    }

    public function index(): void {
        if (isset($_GET['simple']) && $_GET['simple'] === 'true') {
            $somenteAtivos = !isset($_GET['ativos']) || $_GET['ativos'] !== 'false';
            $this->json($this->repo->findSimple($somenteAtivos));
            return;
        }

        $draw = (int) ($_GET['draw'] ?? 1);
        $start = (int) ($_GET['start'] ?? 0);
        $length = (int) ($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = [
            'modalidade_id' => $_GET['modalidade_id'] ?? '',
            'ativo' => $_GET['ativo'] ?? '',
        ];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id): void {
        $treino = $this->repo->findById($id);
        if (!$treino) {
            $this->error("Treino nao encontrado.", 404);
            return;
        }

        $this->json($treino);
    }

    public function store(): void {
        $dto = TreinoDTO::fromArray($this->body());

        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Treino criado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[TreinoController::store] ' . $e->getMessage());
            $this->error("Erro interno ao processar requisicao.", 500);
        }
    }

    public function update(int $id): void {
        $dto = TreinoDTO::fromArray($this->body());

        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Treino atualizado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TreinoController::update] ' . $e->getMessage());
            $this->error("Erro ao atualizar treino.", 500);
        }
    }

    public function cancelar(int $id): void {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Treino desativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TreinoController::cancelar] ' . $e->getMessage());
            $this->error("Erro ao desativar treino.", 500);
        }
    }

    public function reactivate(int $id): void {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Treino reativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TreinoController::reactivate] ' . $e->getMessage());
            $this->error("Erro ao reativar treino.", 500);
        }
    }
}
