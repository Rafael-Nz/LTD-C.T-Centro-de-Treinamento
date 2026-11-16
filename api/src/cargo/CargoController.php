<?php
namespace Cargo;

use Cargo\DTO\CargoDTO;
use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;

class CargoController extends Controller {
    use DataTablesResponseTrait;

    private CargoRepository $repo;
    private CargoService $service;

    public function __construct() {
        $this->repo = new CargoRepository();
        $this->service = new CargoService();
    }

    public function index(): void {
        if ((isset($_GET['simple']) && $_GET['simple'] === 'true') || !isset($_GET['draw'])) {
            $somenteAtivos = !isset($_GET['ativos']) || $_GET['ativos'] !== 'false';
            $this->json($this->repo->findSimple($somenteAtivos));
            return;
        }

        $draw = (int) ($_GET['draw'] ?? 1);
        $start = (int) ($_GET['start'] ?? 0);
        $length = (int) ($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = ['status' => $_GET['status'] ?? ''];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id): void {
        $cargo = $this->repo->findById($id);
        if (!$cargo) {
            $this->error('Cargo nao encontrado.', 404);
            return;
        }

        $cargo['permissoes'] = $this->repo->findPermissionIds($id);
        $cargo['permissoes_slugs'] = $this->repo->findPermissionSlugs($id);

        $this->json($cargo);
    }

    public function permissoes(): void {
        $this->json($this->repo->listAllPermissions());
    }

    public function store(): void {
        $body = $this->body();
        $dto = CargoDTO::fromArray($body);
        $permissoes = isset($body['permissoes']) && is_array($body['permissoes']) ? $body['permissoes'] : [];

        try {
            $id = $this->service->create($dto, $permissoes);
            $this->json(['id' => $id, 'message' => 'Cargo cadastrado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->errorFrom($e, 422);
        } catch (\RuntimeException $e) {
            $this->errorFrom($e, 409);
        } catch (\Throwable $e) {
            error_log('[CargoController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Erro interno ao processar requisicao.', 500);
        }
    }

    public function update(int $id): void {
        $body = $this->body();
        $dto = CargoDTO::fromArray($body);
        $permissoes = isset($body['permissoes']) && is_array($body['permissoes']) ? $body['permissoes'] : null;

        try {
            $this->service->update($id, $dto, $permissoes);
            $this->json(['message' => 'Cargo atualizado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->errorFrom($e, 422);
        } catch (\RuntimeException $e) {
            $this->errorFrom($e, 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Erro ao atualizar cargo.', 500);
        }
    }

    public function deactivate(int $id): void {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Cargo desativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->errorFrom($e, 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::deactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Erro ao desativar cargo.', 500);
        }
    }

    public function reactivate(int $id): void {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Cargo reativado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->errorFrom($e, 404);
        } catch (\Throwable $e) {
            error_log('[CargoController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Erro ao reativar cargo.', 500);
        }
    }
}
