<?php
namespace Local;

use Core\Controller;
use Core\DataTablesResponseTrait;
use Local\DTO\LocalDTO;    
class LocalController extends Controller
{
    use DataTablesResponseTrait;

    private LocalRepository $repo;
    private LocalService    $service;

    public function __construct()
    {
        $this->repo    = new LocalRepository();
        $this->service = new LocalService();
    }

    public function index(): void
    {
        $draw   = (int) ($_GET['draw']          ?? 1);
        $start  = (int) ($_GET['start']         ?? 0);
        $length = (int) ($_GET['length']        ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = ['status' => $_GET['status'] ?? ''];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id): void
    {
        $local = $this->repo->findById($id);
        if (!$local) {
            $this->error("Local de treino não encontrado.", 404);
            return;
        }
        $this->json($local);
    }

    public function store(): void
    {
        $dto = LocalDTO::fromArray($this->body());

        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Local de treino cadastrado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[LocalController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
        }
    }

    public function update(int $id): void
    {
        $dto = LocalDTO::fromArray($this->body());

        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Local de treino atualizado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[LocalController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar local de treino.", 500);
        }
    }

    public function deactivate(int $id): void
    {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Local de treino desativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[LocalController::deactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao desativar local de treino.", 500);
        }
    }

    public function reactivate(int $id): void
    {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Local de treino reativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[LocalController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao reativar local de treino.", 500);
        }
    }
}