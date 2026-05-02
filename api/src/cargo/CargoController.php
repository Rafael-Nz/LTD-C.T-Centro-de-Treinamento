<?php
namespace Cargo;

use Core\Controller;
use Core\DataTablesResponseTrait;
use Cargo\DTO\CargoDTO;

class CargoController extends Controller {
    use DataTablesResponseTrait;

    private CargoRepository $repo;
    private CargoService $service;

    public function __construct() {
        $this->repo = new CargoRepository();
        $this->service = new CargoService();
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
        $cargo = $this->repo->findById($id);
        if (!$cargo) {
            $this->error("Cargo não encontrado.", 404);
            return;
        }
        $this->json($cargo);
    }

    public function store(): void {
        $dto = CargoDTO::fromArray($this->body());
        
        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Cargo cadastrado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[CargoController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
        }
    }

    public function update(int $id): void {
        $dto = CargoDTO::fromArray($this->body());
        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Cargo atualizado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar cargo.", 500);
        }
    }

    public function deactivate(int $id): void {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Cargo desativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::destroy] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao desativar cargo.", 500);
        }
    }

    public function reactivate(int $id): void {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Cargo reativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao reativar cargo.", 500);
        }
    }
}