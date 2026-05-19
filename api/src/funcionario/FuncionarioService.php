<?php
namespace Funcionario;

use Core\Services\Service;
use Usuario\UsuarioService;
use Funcionario\DTO\FuncionarioDTO;
class FuncionarioService extends Service {

    private FuncionarioRepository $funcionarioRepo;
    private UsuarioService $usuarioService;
    
    public function __construct() {
        $this->funcionarioRepo = new FuncionarioRepository();
        $this->usuarioService = new UsuarioService();
    }

    public function create(FuncionarioDTO $dto): int {
        return $this->transaction(function() use ($dto) {
            // Força o tipo de usuário
            $dto->tipo_usuario = 'funcionario';

            // Cria o usuário base (DTO é subclasse de UsuarioDTO)
            $usuarioId = $this->usuarioService->create($dto);

            // Cria o registro específico de funcionário
            $this->funcionarioRepo->create($dto, $usuarioId);

            return $usuarioId;
        });
    }

    public function update(int $id, FuncionarioDTO $dto): void {
        $this->transaction(function() use ($id, $dto) {
            // Atualiza dados do usuário (se houver campos alterados)
            $this->usuarioService->update($id, $dto);

            // Atualiza dados específicos do funcionário
            $this->funcionarioRepo->update($id, $dto);
        });
    }

    public function findById(int $id): ?array {
        $usuario = $this->usuarioService->findById($id);
        if (!$usuario) return null;

        $funcionario = $this->funcionarioRepo->findFuncionarioData($id);
        if (!$funcionario) return null;

        return array_merge(
            $usuario,
            [
                'cargo_id' => $funcionario['cargo_id'],
                'registro_profissional' => $funcionario['registro_profissional'],
                'observacoes' => $funcionario['observacoes']
            ]
        );
    }

    // DELETE (soft)
    public function deactivate(int $id): void {
        $this->usuarioService->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->usuarioService->reactivate($id);
    }
}
