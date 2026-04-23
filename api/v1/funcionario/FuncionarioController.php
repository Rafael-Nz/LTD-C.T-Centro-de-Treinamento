<?php
namespace Funcionario;

use Core\Controller;

class FuncionarioController extends Controller {
    private FuncionarioRepository $repo;

    public function __construct() {
        $this->repo = new FuncionarioRepository();
    }

    // GET /v1/funcionario/         → lista todos
    // GET /v1/funcionario/?id=N    → detalhe de um funcionário
    public function index(array $params = []): void {
        $id = isset($params[0]) ? (int)$params[0] : null;
        if ($id !== null) {
            $func = $this->repo->findById($id);
            if (!$func) {
                $this->error("Funcionário não encontrado.", 404);
            }
            $this->json($func);
        } else {
            $this->json($this->repo->findAll());
        }
    }

    // POST /v1/funcionario/   → cadastra novo funcionário

    public function store(): void {
        $this->only('POST');
        $body = $this->body();

        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }

        $cpf = preg_replace('/\D/', '', $body['cpf']);

        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            if ($this->repo->findByCpf($cpf)) {
                $this->error("CPF já cadastrado.", 409);
            }

            if (!empty($body['email']) && $this->repo->findByEmail($body['email'])) {
                $this->error("E-mail já cadastrado.", 409);
            }
            
            $body['cpf'] = $cpf;
            // Se senha não vier, define null
            $body['senha'] = !empty($body['senha']) ? password_hash($body['senha'], PASSWORD_ARGON2ID) : null;
            
            $id = $this->repo->insert($body);
            $this->json(['success' => true, 'id' => $id, 'message' => 'Funcionário cadastrado com sucesso.']);
        } catch (\Exception $e) {
            $db->rollBack();
            error_log('[FuncionarioController::store] Erro: ' . $e->getMessage());
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            $this->error($e->getMessage(), $code);
        }
    }

    // PUT /v1/funcionario/?id=N   → atualiza funcionário

    public function update(): void {
        $this->only('PUT');
        // Espera id via query string (?id=)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            $this->error('ID do funcionário não informado.', 400);
        }
        $body = $this->body();
        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }
        $cpf = preg_replace('/\D/', '', $body['cpf']);
        // Verifica unicidade de CPF/email para outros funcionários
        $existenteCpf = $this->repo->findByCpf($cpf);
        if ($existenteCpf && (int)$existenteCpf['id'] !== $id) {
            $this->error("CPF já cadastrado em outro funcionário.", 409);
        }
        $existenteEmail = !empty($body['email']) ? $this->repo->findByEmail($body['email']) : null;
        if ($existenteEmail && (int)$existenteEmail['id'] !== $id) {
            $this->error("E-mail já cadastrado em outro funcionário.", 409);
        }
        $body['cpf'] = $cpf;
        $ok = $this->repo->update($id, $body);
        if ($ok) {
            $this->json(['success' => true, 'message' => 'Funcionário atualizado com sucesso.']);
        } else {
            $this->error('Funcionário não encontrado ou dados não alterados.', 404);
        }
    }

    // DELETE /v1/funcionario/?id=N   → desativa funcionário

    public function destroy(): void {
        $this->only('DELETE');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            $this->error('ID do funcionário não informado.', 400);
        }
        $ok = $this->repo->softDelete($id);
        if ($ok) {
            $this->json(['success' => true, 'message' => 'Funcionário desativado com sucesso.']);
        } else {
            $this->error('Funcionário não encontrado.', 404);
        }
    }

    // Validação básica dos dados
    private function validar(array $body): array {
        $erros = [];
        if (empty($body['nome'])) $erros[] = 'Nome obrigatório';
        if (empty($body['sobrenome'])) $erros[] = 'Sobrenome obrigatório';
        if (empty($body['cpf'])) $erros[] = 'CPF obrigatório';
        if (empty($body['data_nascimento'])) $erros[] = 'Data de nascimento obrigatória';
        if (empty($body['cargo_id'])) $erros[] = 'Cargo obrigatório';
        if (empty($body['endereco_id'])) $erros[] = 'Endereço obrigatório';
        return $erros;
    }
}
