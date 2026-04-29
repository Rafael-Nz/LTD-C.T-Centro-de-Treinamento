<?php
namespace Usuario;

use Core\Controller;
use Core\DataTablesResponseTrait;

class UsuarioController extends Controller {
    use DataTablesResponseTrait;
    private UsuarioRepository $repo;
    private UsuarioService $service;
    
    public function __construct() {
        $this->repo = new UsuarioRepository();
        $this->service = new UsuarioService();
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
        $usuario = $this->repo->findById($id);
        if (!$usuario) {
            $this->error("Usuário não encontrado.", 404);
            return;
        }
        $this->json($usuario);
    }

    public function store() {
        $data = $this->body();

        // Validação básica
        if (empty($data['nome']) || empty($data['sobrenome']) || empty($data['cpf']) || empty($data['email']) || empty($data['data_nascimento'])) {
            $this->error("Campos obrigatórios: nome, sobrenome, cpf, email, data_nascimento", 400);
            return;
        }

        try {
            $id = match($data['tipo_usuario']) {
                'aluno' => (new \Aluno\AlunoService())->create($data),
                'funcionario' => (new \Funcionario\FuncionarioService())->create($data),
                default => throw new \Exception("Tipo de usuário inválido.")
            };
            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::store] ' . $e->getMessage());
            $this->error("Erro ao criar usuário: " . $e->getMessage(), 500);
        }
    }

    public function update(int $id) {
        $data = $this->body();

        try {
            $this->service->update($id, $data);
            $this->json(['message' => 'Usuário atualizado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::update] ' . $e->getMessage());
            $this->error("Erro ao atualizar usuário: " . $e->getMessage(), 500);
        }
    }

    public function deactivate(int $id) {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Usuário desativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::destroy] ' . $e->getMessage());
            $this->error("Erro ao desativar usuário: " . $e->getMessage(), 500);
        }
    }

    public function reactivate(int $id) {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Usuário reativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::reactivate] ' . $e->getMessage());
            $this->error("Erro ao reativar usuário: " . $e->getMessage(), 500);
        }
    }
}
