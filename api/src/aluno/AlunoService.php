<?php
namespace Aluno;

use Aluno\DTO\AlunoDTO;
use Core\Services\Service;
use Usuario\UsuarioService;

class AlunoService extends Service {
    private AlunoRepository $alunoRepo;
    private UsuarioService $usuarioService;
    private SequenciaMatriculaRepository $seqRepo;

    public function __construct() {
        $this->alunoRepo = new AlunoRepository();
        $this->usuarioService = new UsuarioService();
        $this->seqRepo = new SequenciaMatriculaRepository();
    }

    public function create(AlunoDTO $dto): int {
        return $this->transaction(function () use ($dto) {
            $this->validateTurmas($dto->turma_ids);

            if (empty($dto->codigo_matricula)) {
                $dto->codigo_matricula = $this->gerarMatricula();
            }

            $dto->tipo_usuario = 'aluno';

            $usuarioId = $this->usuarioService->create($dto);
            $this->alunoRepo->create($dto, $usuarioId);

            return $usuarioId;
        });
    }

    public function update(int $id, AlunoDTO $dto): void {
        $this->transaction(function () use ($id, $dto) {
            $this->validateTurmas($dto->turma_ids);

            $this->usuarioService->update($id, $dto);
            $this->alunoRepo->update($id, $dto);
        });
    }

    public function findById(int $id): ?array {
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

    public function deactivate(int $id): void {
        $this->usuarioService->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->usuarioService->reactivate($id);
    }

    private function gerarMatricula(): string {
        return $this->formatarMatricula($this->seqRepo->next());
    }

    private function formatarMatricula(int $id): string {
        return date('Ym') . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    private function validateTurmas(?array $turmaIds): void {
        if ($turmaIds === null) {
            return;
        }

        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        $turmaIds = array_filter($turmaIds, fn (int $id) => $id > 0);

        if (count($turmaIds) !== count($this->alunoRepo->findExistingTurmaIds($turmaIds))) {
            throw new \InvalidArgumentException("Uma ou mais turmas informadas nao existem.");
        }
    }
}
