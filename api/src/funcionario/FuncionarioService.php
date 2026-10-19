<?php
namespace Funcionario;

use Core\Services\Service;
use Core\Auth\PasswordPolicy;
use Usuario\UsuarioService;
use Funcionario\DTO\FuncionarioDTO;
use Auth\AuthService;
class FuncionarioService extends Service {

    private FuncionarioRepository $funcionarioRepo;
    private UsuarioService $usuarioService;
    private AuthService $authService;
    
    public function __construct() {
        $this->funcionarioRepo = new FuncionarioRepository();
        $this->usuarioService = new UsuarioService();
        $this->authService = new AuthService();
    }

    public function create(FuncionarioDTO $dto): array {
        $senhaInicial = PasswordPolicy::generate();
        $cadastro = $this->transaction(function() use ($dto, $senhaInicial) {
            $dto->senha = $senhaInicial;
            $dto->conta_ativada = false;
            $usuarioExistente = $this->usuarioService->findByEmailOrCpf($dto->email, $dto->cpf);
            if ($usuarioExistente) {
                if ($usuarioExistente['email'] !== $dto->email || $usuarioExistente['cpf'] !== $dto->cpf) {
                    throw new \InvalidArgumentException('O e-mail e o CPF devem ser os mesmos da conta existente.');
                }

                if ($this->funcionarioRepo->findFuncionarioData((int) $usuarioExistente['id'])) {
                    throw new \InvalidArgumentException('Este usuario ja possui um cadastro de funcionario.');
                }

                $this->funcionarioRepo->create($dto, (int) $usuarioExistente['id']);
                return ['id' => (int) $usuarioExistente['id'], 'nova_conta' => false, 'enviar_senha' => true];
            }

            // Força o tipo de usuário
            $dto->tipo_usuario = 'funcionario';

            // Cria o usuário base (DTO é subclasse de UsuarioDTO)
            $usuarioId = $this->usuarioService->create($dto, true, true);

            // Cria o registro específico de funcionário
            $this->funcionarioRepo->create($dto, $usuarioId);

            return ['id' => $usuarioId, 'nova_conta' => true, 'enviar_senha' => true];
        });

        $emailEnviado = false;
        if ($cadastro['enviar_senha']) {
            $emailEnviado = true;
            try {
                $this->authService->sendInitialPassword($cadastro['id'], $senhaInicial);
            } catch (\Throwable $e) {
                $emailEnviado = false;
                error_log('[FuncionarioService::create] Falha ao enviar senha inicial do funcionario ' . $cadastro['id'] . ': ' . $e->getMessage());
            }
        }

        return [
            'id' => $cadastro['id'],
            'email_enviado' => $emailEnviado,
            'conta_reutilizada' => !$cadastro['nova_conta'],
        ];
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
