<?php
namespace Funcionario;

use Core\Service;
use Usuario\UsuarioService;
use Core\Database;

class FuncionarioService extends Service {

    private FuncionarioRepository $funcionarioRepo;
    private UsuarioService $usuarioService;
    
    public function __construct() {
        $this->funcionarioRepo = new FuncionarioRepository();
        $this->usuarioService = new UsuarioService();
    }

    public function create(array $data): int {
        return $this->transaction(function() use ($data) {

            // 1. Montar estrutura para usuário
            $usuarioData = [
                'nome' => $data['nome'] ?? null,
                'sobrenome' => $data['sobrenome'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'email' => $data['email'] ?? null,
                'data_nascimento' => $data['data_nascimento'] ?? null,
                'genero' => $data['genero'] ?? 'O',
                'senha' => $data['senha'] ?? null,
                'tipo_usuario' => 'funcionario',
                'endereco' => $data['endereco'] ?? null,
                'contatos' => $data['contatos'] ?? null
            ];

            // 2. Criar a base do usuário
            $usuarioId = $this->usuarioService->create($usuarioData);

            // 4. Criar o registro de funcionário
            $this->funcionarioRepo->create([
                'usuario_id' => $usuarioId,
                'cargo_id' => $data['cargo_id'] ?? null,
                'registro_profissional' => $data['registro_profissional'] ?? null,
                'observacoes' => $data['observacoes'] ?? null
            ]);

            return $usuarioId;
        });
    }

    // UPDATE
    public function update(int $id, array $data): void {
        $this->funcionarioRepo->update($id, $data);
    }

    // DELETE (soft)
    public function deactivate(int $id): void {
        $this->usuarioService->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->usuarioService->reactivate($id);
    }
}
