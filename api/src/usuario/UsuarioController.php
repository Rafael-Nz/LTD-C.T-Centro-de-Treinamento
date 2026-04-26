<?php
namespace Usuario;

use Core\Controller;

class UsuarioController extends Controller {
    private UsuarioService $service;
    private UsuarioRepository $repo;

    public function __construct() {
        $this->service = new UsuarioService();
        $this->repo = new UsuarioRepository();
    }

    public function index() {
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        if ($length === -1) {
            $length = 10;
        }

        $filters = [
            'status' => $_GET['status'] ?? ''
        ];

        $data = $this->repo->findPaginated($start, $length, $search, $filters);
        $total = $this->repo->countAll();
        $hasActiveFilters = !empty($search) || !empty(array_filter($filters));
        $totalFiltered = $hasActiveFilters 
            ? $this->repo->countFiltered($search, $filters)
            : $total;
            
        $this->json([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
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
            $id = $this->service->create($data);
            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            $this->error("Erro ao criar usuário: " . $e->getMessage(), 500);
        }
    }

    public function update(int $id) {
        $data = $this->body();

        try {
            $this->service->update($id, $data);
            $this->json(['message' => 'Usuário atualizado com sucesso.']);
        } catch (\Throwable $e) {
            $this->error("Erro ao atualizar usuário: " . $e->getMessage(), 500);
        }
    }

    public function destroy(int $id) {
        $this->service->deactivate($id);
        $this->json(['message' => 'Usuário desativado com sucesso.']);
    }

    public function reactivate(int $id) {
        $this->service->reactivate($id);
        $this->json(['message' => 'Usuário reativado com sucesso.']);
    }
}
