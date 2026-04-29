<?php
namespace Funcionario;

use Core\Controller;
use Core\DataTablesResponseTrait;

class FuncionarioController extends Controller {
    use DataTablesResponseTrait;
    private FuncionarioRepository $repo;
    private FuncionarioService $service;

    public function __construct() {
        $this->repo = new FuncionarioRepository();
        $this->service = new FuncionarioService();
    }

    public function index() {
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');
        
        $filters = ['status' => $_GET['status'] ?? ''];
        
        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id) {
        $funcionario = $this->repo->findById($id);
        if (!$funcionario) {
            $this->error("Funcionário não encontrado.", 404);
            return;
        }
        $this->json($funcionario);
    }

    public function store() {
        $data = $this->body();
        try {
            $id = $this->service->create($data);
            $this->json(['id' => $id, 'message' =>  'Funcionário criado com sucesso.' ], 201);
        } catch (\Throwable $e) {
            error_log('[FuncionarioController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
        }
    }

    public function update(int $id) {
        try {
            $this->service->update($id, $this->body());
            $this->json(['message' => 'Funcionário atualizado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[FuncionarioController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar funcionário.", 500);
        }
    }

    public function deactivate(int $id) {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Funcionário desativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[FuncionarioController::deactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao desativar funcionário.", 500);
        }
    }

    public function reactivate(int $id) {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Funcionário reativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[FuncionarioController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao reativar funcionário.", 500);
        }
    }
}
