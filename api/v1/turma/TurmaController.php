<?php
namespace Turma;

use Core\Controller;

class TurmaController extends Controller {

    private TurmaRepository $repo;

    public function __construct() {
        $this->repo = new TurmaRepository();
    }

    // =========================================================================
    // GET /v1/turma/         → lista todas
    // GET /v1/turma/?id=N    → detalhe de uma turma
    // =========================================================================
    public function index(array $params = []): void {
        $id = isset($params[0]) ? (int)$params[0] : null;

        if ($id !== null) {
            $turma = $this->repo->findById($id);
            if (!$turma) {
                $this->error("Turma não encontrada.", 404);
            }
            $this->json($turma);
        } else {
            $this->json($this->repo->findAll());
        }
    }

    // =========================================================================
    // GET /v1/turma/:id/alunos → lista alunos da turma
    // =========================================================================
    public function alunos(array $params = []): void {
        $id = isset($params[0]) ? (int)$params[0] : null;

        if ($id === null) {
            $this->error("ID da turma obrigatório.", 400);
        }

        if (!$this->repo->existsTurma($id)) {
            $this->error("Turma não encontrada.", 404);
        }

        $this->json($this->repo->findAlunosTurma($id));
    }

    // =========================================================================
    // GET /v1/turma/:id/treinos → lista treinos da turma
    // =========================================================================
    public function treinos(array $params = []): void {
        $id = isset($params[0]) ? (int)$params[0] : null;

        if ($id === null) {
            $this->error("ID da turma obrigatório.", 400);
        }

        if (!$this->repo->existsTurma($id)) {
            $this->error("Turma não encontrada.", 404);
        }

        $this->json($this->repo->findTreinosTurma($id));
    }

    // =========================================================================
    // POST /v1/turma/   → cadastra nova turma
    // =========================================================================
    public function store(): void {
        $this->only('POST');

        $body = $this->body();

        // --- Validação básica ---------------------------------------------------
        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }

        // --- Validação de unicidade (nome) ------------------------------------
        if ($this->repo->findByNome(trim($body['nome']))) {
            $this->error("Já existe uma turma com este nome.", 409);
        }

        // --- Validação de instrutor ativo -----------------------------------------------
        if (!$this->repo->verificaInstrutorAtivo((int)$body['instrutor_id'])) {
            $this->error("Instrutor inválido ou inativo.", 422);
        }

        // --- Validação de espaço ativo -----------------------------------------------
        if (!$this->repo->verificaEspacoAtivo((int)$body['espaco_treino_id'])) {
            $this->error("Espaço de treino inválido ou inativo.", 422);
        }

        // --- Validação de capacidade -----------------------------------------------
        if ((int)$body['capacidade_minima'] >= (int)$body['capacidade_maxima']) {
            $this->error("Capacidade mínima deve ser menor que a máxima.", 422);
        }

        try {
            $turmaId = $this->repo->insertTurma([
                'nome'                => trim($body['nome']),
                'descricao'           => trim($body['descricao'] ?? ''),
                'turno'               => $body['turno'],
                'capacidade_minima'   => (int)$body['capacidade_minima'],
                'capacidade_maxima'   => (int)$body['capacidade_maxima'],
                'instrutor_id'        => (int)$body['instrutor_id'],
                'espaco_treino_id'    => (int)$body['espaco_treino_id'],
                'ativo'               => isset($body['ativo']) ? (bool)$body['ativo'] : 1,
            ]);

            $this->json(['id' => $turmaId, 'message' => 'Turma cadastrada com sucesso.'], 201);

        } catch (\Throwable $e) {
            error_log('[TurmaController::store] ' . $e->getMessage());
            $this->error("Erro interno ao cadastrar turma.", 500);
        }
    }

    // =========================================================================
    // PUT /v1/turma/:id   → atualiza turma
    // =========================================================================
    public function update(array $params = []): void {
        $this->only('PUT');

        $id = isset($params[0]) ? (int)$params[0] : null;
        if ($id === null) {
            $this->error("ID obrigatório.", 400);
        }

        $turma = $this->repo->findById($id);
        if (!$turma) {
            $this->error("Turma não encontrada.", 404);
        }

        $body = $this->body();

        // --- Validação básica ---------------------------------------------------
        $erros = $this->validarUpdate($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }

        // --- Validação de nome único (se enviado) -----------------------------------------------
        if (isset($body['nome']) && $body['nome'] !== $turma['nome']) {
            if ($this->repo->findByNome(trim($body['nome']))) {
                $this->error("Já existe uma turma com este nome.", 409);
            }
        }

        // --- Validação de instrutor ativo (se enviado) -----------------------------------------------
        if (isset($body['instrutor_id']) && !$this->repo->verificaInstrutorAtivo((int)$body['instrutor_id'])) {
            $this->error("Instrutor inválido ou inativo.", 422);
        }

        // --- Validação de espaço ativo (se enviado) -----------------------------------------------
        if (isset($body['espaco_treino_id']) && !$this->repo->verificaEspacoAtivo((int)$body['espaco_treino_id'])) {
            $this->error("Espaço de treino inválido ou inativo.", 422);
        }

        // --- Validação de capacidade (se enviado) -----------------------------------------------
        if (isset($body['capacidade_minima']) || isset($body['capacidade_maxima'])) {
            $capMin = isset($body['capacidade_minima']) ? (int)$body['capacidade_minima'] : (int)$turma['capacidade_minima'];
            $capMax = isset($body['capacidade_maxima']) ? (int)$body['capacidade_maxima'] : (int)$turma['capacidade_maxima'];
            
            if ($capMin >= $capMax) {
                $this->error("Capacidade mínima deve ser menor que a máxima.", 422);
            }
        }

        try {
            $data = [];
            if (isset($body['nome'])) $data['nome'] = trim($body['nome']);
            if (isset($body['descricao'])) $data['descricao'] = trim($body['descricao']);
            if (isset($body['turno'])) $data['turno'] = $body['turno'];
            if (isset($body['capacidade_minima'])) $data['capacidade_minima'] = (int)$body['capacidade_minima'];
            if (isset($body['capacidade_maxima'])) $data['capacidade_maxima'] = (int)$body['capacidade_maxima'];
            if (isset($body['instrutor_id'])) $data['instrutor_id'] = (int)$body['instrutor_id'];
            if (isset($body['espaco_treino_id'])) $data['espaco_treino_id'] = (int)$body['espaco_treino_id'];
            if (isset($body['ativo'])) $data['ativo'] = (bool)$body['ativo'] ? 1 : 0;

            if (!$this->repo->updateTurma($id, $data)) {
                $this->error("Nenhum dado foi alterado.", 304);
            }

            $this->json(['id' => $id, 'message' => 'Turma atualizada com sucesso.']);

        } catch (\Throwable $e) {
            error_log('[TurmaController::update] ' . $e->getMessage());
            $this->error("Erro interno ao atualizar turma.", 500);
        }
    }

    // =========================================================================
    // DELETE /v1/turma/:id   → deleta turma
    // =========================================================================
    public function delete(array $params = []): void {
        $this->only('DELETE');

        $id = isset($params[0]) ? (int)$params[0] : null;
        if ($id === null) {
            $this->error("ID obrigatório.", 400);
        }

        $turma = $this->repo->findById($id);
        if (!$turma) {
            $this->error("Turma não encontrada.", 404);
        }

        try {
            if (!$this->repo->deleteTurma($id)) {
                $this->error("Erro ao deletar turma.", 500);
            }

            $this->json(['message' => 'Turma deletada com sucesso.']);

        } catch (\Throwable $e) {
            error_log('[TurmaController::delete] ' . $e->getMessage());
            $this->error("Erro interno ao deletar turma.", 500);
        }
    }

    // =========================================================================
    // Validações
    // =========================================================================

    private function validar(array $data): array {
        $erros = [];

        if (empty($data['nome'])) {
            $erros[] = "Nome é obrigatório.";
        } elseif (strlen($data['nome']) > 100) {
            $erros[] = "Nome não pode exceder 100 caracteres.";
        }

        if (empty($data['turno'])) {
            $erros[] = "Turno é obrigatório (manha, tarde ou noite).";
        } elseif (!in_array($data['turno'], ['manha', 'tarde', 'noite'])) {
            $erros[] = "Turno inválido. Use: manha, tarde ou noite.";
        }

        if (empty($data['capacidade_minima'])) {
            $erros[] = "Capacidade mínima é obrigatória.";
        } elseif (!is_numeric($data['capacidade_minima']) || $data['capacidade_minima'] < 1) {
            $erros[] = "Capacidade mínima deve ser um número positivo.";
        }

        if (empty($data['capacidade_maxima'])) {
            $erros[] = "Capacidade máxima é obrigatória.";
        } elseif (!is_numeric($data['capacidade_maxima']) || $data['capacidade_maxima'] < 1) {
            $erros[] = "Capacidade máxima deve ser um número positivo.";
        }

        if (empty($data['instrutor_id'])) {
            $erros[] = "Instrutor é obrigatório.";
        } elseif (!is_numeric($data['instrutor_id']) || $data['instrutor_id'] < 1) {
            $erros[] = "ID do instrutor inválido.";
        }

        if (empty($data['espaco_treino_id'])) {
            $erros[] = "Espaço de treino é obrigatório.";
        } elseif (!is_numeric($data['espaco_treino_id']) || $data['espaco_treino_id'] < 1) {
            $erros[] = "ID do espaço de treino inválido.";
        }

        if (isset($data['descricao']) && strlen($data['descricao']) > 65535) {
            $erros[] = "Descrição muito longa.";
        }

        return $erros;
    }

    private function validarUpdate(array $data): array {
        // Validação menos rigorosa para UPDATE
        $erros = [];

        if (isset($data['nome'])) {
            if (empty($data['nome'])) {
                $erros[] = "Nome não pode ser vazio.";
            } elseif (strlen($data['nome']) > 100) {
                $erros[] = "Nome não pode exceder 100 caracteres.";
            }
        }

        if (isset($data['turno'])) {
            if (!in_array($data['turno'], ['manha', 'tarde', 'noite'])) {
                $erros[] = "Turno inválido. Use: manha, tarde ou noite.";
            }
        }

        if (isset($data['capacidade_minima'])) {
            if (!is_numeric($data['capacidade_minima']) || $data['capacidade_minima'] < 1) {
                $erros[] = "Capacidade mínima deve ser um número positivo.";
            }
        }

        if (isset($data['capacidade_maxima'])) {
            if (!is_numeric($data['capacidade_maxima']) || $data['capacidade_maxima'] < 1) {
                $erros[] = "Capacidade máxima deve ser um número positivo.";
            }
        }

        if (isset($data['instrutor_id'])) {
            if (!is_numeric($data['instrutor_id']) || $data['instrutor_id'] < 1) {
                $erros[] = "ID do instrutor inválido.";
            }
        }

        if (isset($data['espaco_treino_id'])) {
            if (!is_numeric($data['espaco_treino_id']) || $data['espaco_treino_id'] < 1) {
                $erros[] = "ID do espaço de treino inválido.";
            }
        }

        if (isset($data['descricao']) && strlen($data['descricao']) > 65535) {
            $erros[] = "Descrição muito longa.";
        }

        return $erros;
    }
}
