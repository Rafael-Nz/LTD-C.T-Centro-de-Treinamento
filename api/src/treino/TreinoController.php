<?php
namespace Treino;

use Core\Controller;
use Core\DataTablesResponseTrait;
use Treino\DTO\TreinoDTO;

class TreinoController extends Controller {
    use DataTablesResponseTrait;

    private TreinoRepository $repo;
    private TreinoService    $service;

    public function __construct() {
        $this->repo    = new TreinoRepository();
        $this->service = new TreinoService();
    }

    public function index(): void {
        $draw   = (int) ($_GET['draw']          ?? 1);
        $start  = (int) ($_GET['start']         ?? 0);
        $length = (int) ($_GET['length']        ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = [
            'status'    => $_GET['status']    ?? '',
            'turma_id'  => $_GET['turma_id']  ?? '',
            'espaco_id' => $_GET['espaco_id'] ?? '',
        ];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id): void {
        $treino = $this->repo->findById($id);
        if (!$treino) {
            $this->error("Treino não encontrado.", 404);
            return;
        }
        $this->json($treino);
    }

    public function store(): void {
        $dto = TreinoDTO::fromArray($this->body());

        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Treino agendado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            error_log('[TreinoController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
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
            error_log('[TreinoController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar treino.", 500);
        }
    }

    public function cancelar(int $id): void {
        try {
            $this->service->cancelar($id);
            $this->json(['message' => 'Treino cancelado com sucesso.']);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[TreinoController::cancelar] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao cancelar treino.", 500);
        }
    }
}