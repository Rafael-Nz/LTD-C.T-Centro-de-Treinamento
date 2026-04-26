<?php
namespace Cargo;

use Core\Controller;

class CargoController extends Controller {

    private CargoRepository $repo;

    public function __construct() {
        $this->repo = new CargoRepository();
    }

    // =========================================================================
    // GET /api/cargos/        → lista todos
    // =========================================================================
    public function index(): void {
        $draw   = isset($_GET['draw'])   ? (int)$_GET['draw']   : 1;
        $start  = isset($_GET['start'])  ? (int)$_GET['start']  : 0;
        $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        
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
            "draw"            => $draw,
            "recordsTotal"    => $total,
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        ]);
    }

    // =========================================================================
    // GET /api/cargos/{id}    → detalhe de um cargo
    // =========================================================================
    public function show(int $id): void {
        $cargo = $this->repo->findById($id);
        if (!$cargo) {
            $this->error("Cargo não encontrado.", 404);
            return;
        }
        $this->json($cargo);
    }

    // =========================================================================
    // POST /api/cargos/   → cadastra novo cargo
    // =========================================================================
    public function store(): void {
        $this->only('POST');

        $body = $this->body();

        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
            return;
        }

        if ($this->repo->findByNome($body['nome'])) {
            $this->error("Já existe um cargo com este nome.", 409);
            return;
        }

        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            $cargoId = $this->repo->insert([
                'nome'        => trim($body['nome']),
                'descricao'   => $body['descricao']   ?? null,
                'salario_base'=> $body['salario_base'] ?? 0,
                'ativo'       => 1,
            ]);

            $db->commit();

            $this->json(['id' => $cargoId, 'message' => 'Cargo cadastrado com sucesso.'], 201);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[CargosController::store] ' . $e->getMessage());
            $this->error("Erro interno ao cadastrar cargo.", 500);
        }
    }

    // =========================================================================
    // PUT /api/cargos/{id}   → atualiza cargo
    // =========================================================================
    public function update(int $id): void {
        $this->only('PUT');

        if (!$this->repo->findById($id)) {
            $this->error("Cargo não encontrado.", 404);
            return;
        }

        $body = $this->body();

        // Toggle de status
        if (array_key_exists('status', $body) && count($body) === 1) {
            $ativo = ($body['status'] === 'ativo') ? 1 : 0;
            $this->repo->toggleAtivo($id, $ativo);
            $this->json(['message' => 'Status atualizado com sucesso.']);
            return;
        }

        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
            return;
        }

        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            $this->repo->update($id, [
                'nome'        => trim($body['nome']),
                'descricao'   => $body['descricao']   ?? null,
                'salario_base'=> $body['salario_base'] ?? 0,
            ]);

            $db->commit();

            $this->json(['message' => 'Cargo atualizado com sucesso.']);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[CargosController::update] ' . $e->getMessage());
            $this->error("Erro interno ao atualizar cargo.", 500);
        }
    }

    // =========================================================================
    // DELETE /api/cargos/{id}  → desativa cargo
    // =========================================================================
    public function destroy(int $id): void {
        $this->only('DELETE');

        if (!$this->repo->findById($id)) {
            $this->error("Cargo não encontrado.", 404);
            return;
        }

        $this->repo->deactivate($id);
        $this->json(['message' => 'Cargo desativado com sucesso.']);
    }

    public function reactivate(int $id): void {
        $this->only('PUT');

        if (!$this->repo->findById($id)) {
            $this->error("Cargo não encontrado.", 404);
            return;
        }

        if ($this->repo->reactivate($id)) {
            $this->json(['message' => 'Cargo reativado com sucesso.']);
        } else {
            $this->error("Erro ao reativar cargo no banco de dados.", 500);
        }
    }

    private function validar(array $data): array {
        $erros = [];
        if (empty($data['nome'])) {
            $erros[] = "Nome do cargo é obrigatório.";
        }
        return $erros;
    }
    
}
