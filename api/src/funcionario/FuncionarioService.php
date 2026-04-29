<?php
namespace Funcionario;

use Usuario\UsuarioService;
use Core\Database;

class FuncionarioService {

    private FuncionarioRepository $funcionarioRepo;
    private UsuarioService $usuarioService;
    
    public function __construct() {
        $this->funcionarioRepo = new FuncionarioRepository();
        $this->usuarioService = new UsuarioService();
    }

    public function create(array $data): int {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // 1. Montar estrutura para usuário
            $usuarioData = [
                ...$data,
                'tipo_usuario' => 'funcionario'
            ];
            unset($usuarioData['cargo_id'], $usuarioData['registro_profissional'], $usuarioData['observacoes']);

            // 3. Criar usuário
            $usuarioId = $this->usuarioService->create($usuarioData);

            // 4. Criar funcionário
            $funcionarioData = [
                'usuario_id' => $usuarioId,
                'cargo_id' => $data['cargo_id'] ?? null,
                'registro_profissional' => $data['registro_profissional'] ?? null,
                'observacoes' => $data['observacoes'] ?? null
            ];

            $this->funcionarioRepo->create($funcionarioData);

            $db->commit();
            return $usuarioId;

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
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
