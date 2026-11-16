<?php
namespace Funcionario;

use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;
use Funcionario\DTO\FuncionarioDTO;

class FuncionarioController extends Controller {
    use DataTablesResponseTrait;
    private FuncionarioRepository $repo;
    private FuncionarioService $service;

    public function __construct() {
        $this->repo = new FuncionarioRepository();
        $this->service = new FuncionarioService();
    }

    public function index() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'cargo_id' => $_GET['cargo_id'] ?? '',
            'cargos'   => isset($_GET['cargos']) ? explode(',', $_GET['cargos']) : null,
        ];

        if (isset($_GET['simple']) && $_GET['simple'] == 'true') {
            $data = $this->repo->findSimple($filters);
            return $this->json(['success' => true, 'data' => $data]);
        }
        
        $draw   = (int)($_GET['draw']   ?? 1);
        $start  = (int)($_GET['start']  ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');
        
        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id) {
        $funcionario = $this->service->findById($id);
        if (!$funcionario) {
            $this->error("Funcionário não encontrado.", 404);
            return;
        }
        $this->json($funcionario);
    }

    public function store() {
        $dto = FuncionarioDTO::fromArray($this->body());
        try {
            $result = $this->service->create($dto);
            $message = $result['email_enviado']
                ? 'Funcionário criado com sucesso. O convite de ativação foi enviado para o e-mail cadastrado.'
                : 'Funcionário criado com sucesso, mas o convite não foi enviado. Verifique a configuração de e-mail e o log do servidor.';
            $this->json(['id' => $result['id'], 'email_enviado' => $result['email_enviado'], 'conta_reutilizada' => false, 'message' => $message], 201);
        } catch (\InvalidArgumentException $e) {
            $this->errorFrom($e, 422);
        } catch (\PDOException $e) {
            $errorCode = (int) ($e->errorInfo[1] ?? 0);
            $details = strtolower($e->getMessage());

            if ($errorCode === 1062) {
                $field = str_contains($details, 'cpf')
                    ? 'CPF'
                    : (str_contains($details, 'email') ? 'e-mail' : 'CPF ou e-mail');
                $this->error("Este {$field} já está cadastrado.", 409);
            }

            error_log('[FuncionarioController::store] ' . $e->getMessage());
            $this->error('Não foi possível salvar o funcionário no banco de dados.', 500);
        } catch (\Throwable $e) {
            error_log('[FuncionarioController::store] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->error("Erro interno ao salvar funcionário.", 500);
        }
    }

    public function update(int $id) {
        $dto = FuncionarioDTO::fromArray($this->body());
        try {
            $this->service->update($id, $dto);
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
