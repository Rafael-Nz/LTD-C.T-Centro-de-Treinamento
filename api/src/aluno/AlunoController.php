<?php
namespace Aluno;

use Aluno\DTO\AlunoDTO;
use Core\Auth\Auth;
use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;

class AlunoController extends Controller {
    use DataTablesResponseTrait;

    private AlunoRepository $repo;
    private AlunoService $service;

    public function __construct() {
        $this->repo = new AlunoRepository();
        $this->service = new AlunoService();
    }

    public function index() {
        $draw = (int) ($_GET['draw'] ?? 1);
        $start = (int) ($_GET['start'] ?? 0);
        $length = (int) ($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = ['status' => $_GET['status'] ?? ''];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id) {
        $aluno = $this->service->findById($id);
        if (!$aluno) {
            $this->error("Aluno nao encontrado.", 404);
            return;
        }

        $this->json($aluno);
    }

    public function store() {
        $dto = AlunoDTO::fromArray($this->body());
        $dto->cadastrado_por = Auth::user('id');

        try {
            $id = $this->service->create($dto);
            $this->json(['id' => $id, 'message' => 'Aluno criado com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[AlunoController::store] ' . $e->getMessage());
            $this->error("Erro interno ao processar requisicao.", 500);
        }
    }

    public function update(int $id) {
        $dto = AlunoDTO::fromArray($this->body());

        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Aluno atualizado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[AlunoController::update] ' . $e->getMessage());
            $this->error("Erro ao atualizar aluno.", 500);
        }
    }

    public function deactivate(int $id) {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Aluno desativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[AlunoController::deactivate] ' . $e->getMessage());
            $this->error("Erro ao desativar aluno.", 500);
        }
    }

    public function reactivate(int $id) {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Aluno reativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[AlunoController::reactivate] ' . $e->getMessage());
            $this->error("Erro ao reativar aluno.", 500);
        }
    }
}
