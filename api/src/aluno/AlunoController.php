<?php
namespace Aluno;

use Core\Controller;
use Core\DataTablesResponseTrait;

class AlunoController extends Controller {
    use DataTablesResponseTrait;

    private AlunoRepository $repo;
    private AlunoService $service;

    public function __construct() {
        $this->repo = new AlunoRepository();
        $this->service = new AlunoService();
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
        $aluno = $this->repo->findById($id);
        if (!$aluno) {
            $this->error("Aluno não encontrado.", 404);
            return;
        }
        $this->json($aluno);
    }

    public function store() {
        $data = $this->body();
        try {
            $id = $this->service->create($data);
            $this->json(['id' => $id, 'message' =>  'Aluno criado com sucesso.' ], 201);
        } catch (\Throwable $e) {
            error_log('[AlunoController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao processar requisição.", 500);
        }
    }

    public function update(int $id) {
        try {
            $this->service->update($id, $this->body());
            $this->json(['message' => 'Aluno atualizado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[AlunoController::update] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao atualizar aluno.", 500);
        }
    }

    public function deactivate(int $id) {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Aluno desativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[AlunoController::deactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao desativar aluno.", 500);
        }
    }

    public function reactivate(int $id) {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Aluno reativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[AlunoController::reactivate] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro ao reativar aluno.", 500);
        }
    }

}