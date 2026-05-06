<?php
namespace Treino;

use Core\Service;
use Treino\DTO\TreinoDTO;

class TreinoService extends Service {
    private TreinoRepository $repo;

    public function __construct() {
        $this->repo = new TreinoRepository();
    }

    public function create(TreinoDTO $dto): int {
        $this->validate($dto);

        return $this->transaction(function () use ($dto) {
            if (!$this->repo->verificaTurmaExiste($dto->turma_id)) {
                throw new \RuntimeException("Turma não encontrada.");
            }

            if (!$this->repo->verificaEspacoAtivo($dto->espaco_id)) {  
                throw new \RuntimeException("Local de treino inválido ou inativo.");
            }

            if ($this->repo->verificaConflito($dto->espaco_id, $dto->data_hora_inicio, $dto->data_hora_fim)) { 
                throw new \RuntimeException("Este local já está ocupado no horário informado.");
            }

            return $this->repo->create($dto);
        });
    }

    public function update(int $id, TreinoDTO $dto): void {
        $this->validate($dto);

        $this->transaction(function () use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Treino não encontrado.");
            }

            if ($existing['status'] === 'cancelado') {
                throw new \RuntimeException("Não é possível editar um treino cancelado.");
            }

            if (!$this->repo->verificaTurmaExiste($dto->turma_id)) {
                throw new \RuntimeException("Turma não encontrada.");
            }

            if (!$this->repo->verificaEspacoAtivo($dto->espaco_id)) { 
                throw new \RuntimeException("Local de treino inválido ou inativo.");
            }

            if ($this->repo->verificaConflito($dto->espaco_id, $dto->data_hora_inicio, $dto->data_hora_fim, $id)) {  // ← padronizado
                throw new \RuntimeException("Este local já está ocupado no horário informado.");
            }

            $this->repo->update($id, $dto);
        });
    }

    public function cancelar(int $id): void {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException("Treino não encontrado.");
        }

        if ($existing['status'] === 'cancelado') {
            throw new \RuntimeException("Este treino já está cancelado.");
        }

        $this->repo->cancelar($id);
    }

    private function validate(TreinoDTO $dto): void {
        if (empty($dto->turma_id) || $dto->turma_id < 1) {
            throw new \InvalidArgumentException("Turma é obrigatória.");
        }

        if (empty($dto->espaco_id) || $dto->espaco_id < 1) { 
            throw new \InvalidArgumentException("Espaço de treino é obrigatório.");
        }

        if (empty($dto->data_hora_inicio)) {
            throw new \InvalidArgumentException("Data/hora de início é obrigatória.");
        }

        if (empty($dto->data_hora_fim)) {
            throw new \InvalidArgumentException("Data/hora de término é obrigatória.");
        }

        if (strtotime($dto->data_hora_inicio) >= strtotime($dto->data_hora_fim)) {
            throw new \InvalidArgumentException("Data/hora de início deve ser anterior ao término.");
        }

        $statusValidos = ['agendado', 'concluido', 'cancelado'];
        if (!in_array($dto->status, $statusValidos, true)) {
            throw new \InvalidArgumentException("Status inválido. Use: agendado, concluido ou cancelado.");
        }
    }
}