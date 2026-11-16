<?php

namespace Aluno;

use Aluno\DTO\AlunoDTO;
use Auth\AuthService;
use Core\Auth\PasswordPolicy;
use Core\Services\Service;
use Usuario\UsuarioService;

class AlunoService extends Service
{
    private AlunoRepository $alunoRepo;
    private UsuarioService $usuarioService;
    private SequenciaMatriculaRepository $seqRepo;
    private AuthService $authService;

    public function __construct()
    {
        $this->alunoRepo = new AlunoRepository();
        $this->usuarioService = new UsuarioService();
        $this->seqRepo = new SequenciaMatriculaRepository();
        $this->authService = new AuthService();
    }

    public function create(AlunoDTO $dto): array
    {
        $senhaInicial = PasswordPolicy::generate();

        $cadastro = $this->transaction(function () use ($dto, $senhaInicial) {
            $this->validateTurmas($dto->turma_ids);

            if (empty($dto->codigo_matricula)) {
                $dto->codigo_matricula = $this->gerarMatricula();
            }

            $dto->senha = $senhaInicial;
            $dto->conta_ativada = false;
            $dto->tipo_usuario = 'aluno';

            $usuarioId = $this->usuarioService->create($dto, true, true);
            $this->alunoRepo->create($dto, $usuarioId);

            return ['id' => $usuarioId];
        });

        $emailEnviado = false;
        try {
            $this->authService->sendInitialPassword($cadastro['id'], $senhaInicial);
            $emailEnviado = true;
        } catch (\Throwable $e) {
            // O cadastro já foi confirmado no banco. Não transforme uma falha
            // de SMTP em erro 500 nem faça o operador cadastrar o aluno de novo.
            $emailEnviado = false;
            error_log('[AlunoService::create] Falha ao enviar senha inicial do aluno ' . $cadastro['id'] . ': ' . $e->getMessage());
        }

        return [
            'id' => $cadastro['id'],
            'email_enviado' => $emailEnviado,
            'conta_reutilizada' => false,
        ];
    }

    public function update(int $id, AlunoDTO $dto): void
    {
        $this->transaction(function () use ($id, $dto) {
            $this->validateTurmas($dto->turma_ids);

            $this->usuarioService->update($id, $dto);
            $this->alunoRepo->update($id, $dto);
        });
    }

    public function findById(int $id): ?array
    {
        $usuario = $this->usuarioService->findById($id);
        if (!$usuario) {
            return null;
        }

        $aluno = $this->alunoRepo->findAlunoData($id);
        if (!$aluno) {
            return null;
        }

        return array_merge($usuario, [
            'data_matricula' => $aluno['data_matricula'],
            'codigo_matricula' => $aluno['codigo_matricula'],
            'turmas' => $aluno['turmas'] ?? [],
        ]);
    }

    public function deactivate(int $id): void
    {
        $this->usuarioService->deactivate($id);
    }

    public function reactivate(int $id): void
    {
        $this->usuarioService->reactivate($id);
    }

    private function gerarMatricula(): string
    {
        return $this->formatarMatricula($this->seqRepo->next());
    }

    private function formatarMatricula(int $id): string
    {
        return date('Ym') . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    private function validateTurmas(?array $turmaIds): void
    {
        if ($turmaIds === null) {
            return;
        }

        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        $turmaIds = array_filter($turmaIds, fn(int $id) => $id > 0);

        if (count($turmaIds) !== count($this->alunoRepo->findExistingTurmaIds($turmaIds))) {
            throw new \InvalidArgumentException("Uma ou mais turmas informadas nao existem.");
        }
    }
}
