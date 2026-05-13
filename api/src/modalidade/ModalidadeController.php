<?php
namespace Modalidade;

use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;
use Modalidade\DTO\ModalidadeDTO;

class ModalidadeController extends Controller {
    use DataTablesResponseTrait;

    private ModalidadeRepository $repo;
    private ModalidadeService $service;

    public function __construct() {
        $this->repo = new ModalidadeRepository();
        $this->service = new ModalidadeService();
    }

    public function index(): void {
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');
        
        $filters = ['status' => $_GET['status'] ?? ''];
        
        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id): void {
        $modalidade = $this->repo->findById($id);
        if (!$modalidade) {
            $this->error("Modalidade não encontrada.", 404);
            return;
        }
        $this->json($modalidade);
    }

    public function store(): void {
        $dto = ModalidadeDTO::fromArray($this->body());
        
        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Modadalidade cadastrada com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[ModalidadeController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
        }
    }

    public function update(int $id): void {
        $dto = ModalidadeDTO::fromArray($this->body());
        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Modadalidade atualizada com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[ModalidadeController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar modalidade.", 500);
        }
    }

    public function deactivate(int $id): void {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Modalidade desativada com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[ModalidadeController::destroy] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao desativar modalidade.", 500);
        }
    }

    public function reactivate(int $id): void {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Modalidade reativada com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[ModalidadeController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao reativar modalidade.", 500);
        }
    }
}
