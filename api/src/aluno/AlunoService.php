<?php
namespace Aluno;

use Core\Service;
use Usuario\UsuarioService;
use Aluno\DTO\AlunoDTO;

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
        return $this->transaction(function() use ($dto) {
            if (empty($dto->codigo_matricula)) {
                $dto->codigo_matricula = $this->gerarMatricula();
            }

            $dto->tipo_usuario = 'aluno';

            $usuarioId = $this->usuarioService->create($dto);

            // 4. Criar o registro na tabela 'aluno'
            $this->alunoRepo->create($dto, $usuarioId);

            return $usuarioId;
        });
    }

    public function update(int $id, AlunoDTO $dto): void {
        $this->transaction(function() use ($id, $dto) {
            $this->usuarioService->update($id, $dto);

            $this->alunoRepo->update($id, $dto);
        });
    }

    public function findById(int $id): ?array {
        $usuario = $this->usuarioService->findById($id);
        if (!$usuario) return null;

        $aluno = $this->alunoRepo->findAlunoData($id);
        if (!$aluno) return null;

        return array_merge(
            $usuario,
            [
                'data_matricula' => $aluno['data_matricula'],
                'codigo_matricula' => $aluno['codigo_matricula']
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

    private function gerarMatricula(): string {
        return $this->formatarMatricula($this->seqRepo->next());
    }

    private function formatarMatricula(int $id): string {
        return date('Ym') . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
}