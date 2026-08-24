<?php
namespace Usuario;

use Core\Auth\Auth;
use Core\DataTables\DataTablesResponseTrait;
use Core\Http\Controller;
use Usuario\DTO\UsuarioDTO;

class UsuarioController extends Controller {
    use DataTablesResponseTrait;

    private UsuarioRepository $repo;
    private UsuarioService $service;

    public function __construct() {
        $this->repo = new UsuarioRepository();
        $this->service = new UsuarioService();
    }

    public function index() {
        $draw = (int) ($_GET['draw'] ?? 1);
        $start = (int) ($_GET['start'] ?? 0);
        $length = (int) ($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $filters = ['status' => $_GET['status'] ?? ''];

        $this->dataTablesResponse($this->repo, $draw, $start, $length, $search, $filters);
    }

    public function show(int $id) {
        $usuario = $this->service->findById($id);
        if (!$usuario) {
            $this->error('Usuario nao encontrado.', 404);
            return;
        }

        $this->json($usuario);
    }

    public function me() {
        $usuarioId = Auth::id();
        if (!$usuarioId) {
            $this->error('Sessao expirada ou nao encontrada.', 401);
            return;
        }

        $usuario = $this->service->findById($usuarioId);
        if (!$usuario) {
            $this->error('Usuario nao encontrado.', 404);
            return;
        }

        $this->json($usuario);
    }

    public function store() {
        $dto = UsuarioDTO::fromArray($this->body());

        if (empty($dto->nome) || empty($dto->sobrenome) || empty($dto->cpf) || empty($dto->email) || empty($dto->data_nascimento)) {
            $this->error('Campos obrigatorios: nome, sobrenome, cpf, email, data_nascimento', 400);
            return;
        }

        try {
            $id = match ($dto->tipo_usuario) {
                'aluno' => (new \Aluno\AlunoService())->create(\Aluno\DTO\AlunoDTO::fromArray($dto->toArray())),
                'funcionario' => (new \Funcionario\FuncionarioService())->create(\Funcionario\DTO\FuncionarioDTO::fromArray($dto->toArray())),
                default => throw new \Exception('Tipo de usuario invalido.')
            };

            $this->json(['id' => $id], 201);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::store] ' . $e->getMessage());
            $this->error('Erro ao criar usuario: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id) {
        $dto = UsuarioDTO::fromArray($this->body());

        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Usuario atualizado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::update] ' . $e->getMessage());
            $this->error('Erro ao atualizar usuario: ' . $e->getMessage(), 500);
        }
    }

    public function deactivate(int $id) {
        try {
            $this->service->deactivate($id);
            $this->json(['message' => 'Usuario desativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::destroy] ' . $e->getMessage());
            $this->error('Erro ao desativar usuario: ' . $e->getMessage(), 500);
        }
    }

    public function reactivate(int $id) {
        try {
            $this->service->reactivate($id);
            $this->json(['message' => 'Usuario reativado com sucesso.']);
        } catch (\Throwable $e) {
            error_log('[UsuarioController::reactivate] ' . $e->getMessage());
            $this->error('Erro ao reativar usuario: ' . $e->getMessage(), 500);
        }
    }
}
