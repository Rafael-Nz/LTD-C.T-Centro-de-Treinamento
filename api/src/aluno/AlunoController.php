<?php
namespace Aluno;

use Core\Controller;

class AlunoController extends Controller {
    private AlunoService $service;
    private AlunoRepository $repo;

    public function __construct() {
        $this->service = new AlunoService();
        $this->repo = new AlunoRepository();
    }

    public function index() {
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        if ($length === -1) {
            $length = 10;
        }

        // Captura de Filtros Adicionais
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];

        // Execução das consultas
        $data = $this->repo->findPaginated($start, $length, $search, $filters);
            
        // Total geral sem filtros
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
        $aluno = $this->repo->findById($id);
        if (!$aluno) {
            $this->error("Aluno não encontrado.", 404);
            return;
        }
        $this->json($aluno);
    }

    public function store() {
        $data = $this->body();

        $id = $this->service->create($data);

        $this->json(['id' => $id], 201);
    }

    public function update(int $id) {
        $this->service->update($id, $this->body());

        $this->json(['message' => 'Atualizado']);
    }

    public function destroy(int $id) {
        $this->service->delete($id);
        $this->json(['message' => 'Desativado']);
    }

    public function reactivate(int $id) {
        $this->service->reactivate($id);
        $this->json(['message' => 'Reativado']);
    }

}