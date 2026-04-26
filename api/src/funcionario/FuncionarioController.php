<?php
namespace Funcionario;

use Core\Controller;

class FuncionarioController extends Controller {
    private FuncionarioRepository $repo;

    public function __construct() {
        $this->repo = new FuncionarioRepository();
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
        $funcionario = $this->repo->findById($id);
        if (!$funcionario) {
            $this->error("Funcionário não encontrado.", 404);
            return;
        }
        $this->json($funcionario);
    }

    public function store() {
        $data = $this->body();
        // TODO: Implementar lógica de criação
        $this->json(['message' => 'Não implementado ainda'], 501);
    }

    public function update(int $id) {
        // TODO: Implementar lógica de atualização
        $this->json(['message' => 'Não implementado ainda'], 501);
    }

    public function destroy(int $id) {
        // TODO: Implementar lógica de exclusão
        $this->json(['message' => 'Não implementado ainda'], 501);
    }
}
