<?php
namespace Turma;

use Core\Service;
use Turma\DTO\TurmaDTO;

class TurmaService extends Service {
    private TurmaRepository $repository;

    public function __construct() {
        $this->repository = new TurmaRepository();
    }

    /**
     * Cria uma nova turma com todas as validações
     * @throws \Exception Se validação ou insert falhar
     */
    public function create(TurmaDTO $dto): int {
        return $this->transaction(function () use ($dto) {
            $this->validateForCreate($dto);

            if ($this->repository->existsByNomeExcluding($dto->nome, 0)) {
                throw new \Exception("Já existe uma turma com este nome.");
            }

            if (!$this->repository->verificaInstrutorAtivo($dto->instrutor_id)) {
                throw new \Exception("Instrutor inválido ou inativo.");
            }

            if (!$this->repository->verificaModalidadeAtiva($dto->modalidade_id)) {
                throw new \Exception("Modalidade inválida ou inativa.");
            }

            $this->validateCapacity($dto->capacidade_minima, $dto->capacidade_maxima);

            return $this->repository->create($dto);
        });
    }

    /**
     * Atualiza uma turma existente com validações parciais
     * @throws \Exception Se turma não existir ou validações falharem
     */
    public function update(int $id, TurmaDTO $dto): void {
        $this->transaction(function () use ($id, $dto) {
            $turmaExistente = $this->repository->findById($id);
            if (!$turmaExistente) {
                throw new \Exception("Turma não encontrada.");
            }

            $this->validateForUpdate($dto);

            if ($dto->nome !== null && $dto->nome !== $turmaExistente['nome']) {
                if ($this->repository->existsByNomeExcluding($dto->nome, $id)) {
                    throw new \Exception("Já existe uma turma com este nome.");
                }
            }

            if ($dto->instrutor_id !== null && $dto->instrutor_id !== $turmaExistente['instrutor_id']) {
                if (!$this->repository->verificaInstrutorAtivo($dto->instrutor_id)) {
                    throw new \Exception("Instrutor inválido ou inativo.");
                }
            }

            if ($dto->modalidade_id !== null && $dto->modalidade_id !== $turmaExistente['modalidade_id']) {
                if (!$this->repository->verificaModalidadeAtiva($dto->modalidade_id)) {
                    throw new \Exception("Modalidade inválida ou inativa.");
                }
            }

            if ($dto->capacidade_minima !== null || $dto->capacidade_maxima !== null) {
                $capMin = $dto->capacidade_minima ?? (int)$turmaExistente['capacidade_minima'];
                $capMax = $dto->capacidade_maxima ?? (int)$turmaExistente['capacidade_maxima'];
                $this->validateCapacity($capMin, $capMax);
            }

            $this->repository->update($id, $dto);
        });
    }

    /**
     * Obtém uma turma por ID com todos os dados relacionados
     */
    public function findById(int $id): ?array {
        return $this->repository->findById($id);
    }

    /**
     * Lista todas as turmas ativas
     */
    public function findAll(): array {
        return $this->repository->findAll();
    }

    /**
     * Desativa uma turma (soft delete)
     * @throws \Exception Se turma não existir
     */
    public function deactivate(int $id): void {
        $this->transaction(function () use ($id) {
            if (!$this->repository->exists($id)) {
                throw new \Exception("Turma não encontrada.");
            }
            $this->repository->deactivate($id);
        });
    }

    /**
     * Reativa uma turma
     * @throws \Exception Se turma não existir
     */
    public function reactivate(int $id): void {
        $this->transaction(function () use ($id) {
            if (!$this->repository->exists($id)) {
                throw new \Exception("Turma não encontrada.");
            }
            $this->repository->reactivate($id);
        });
    }

    // =========================================================================
    // VALIDAÇÕES
    // =========================================================================

    private function validateForCreate(TurmaDTO $dto): void {
        $erros = [];

        if (empty($dto->nome)) {
            $erros[] = "Nome é obrigatório.";
        } elseif (strlen($dto->nome) > 100) {
            $erros[] = "Nome não pode exceder 100 caracteres.";
        }

        if (empty($dto->turno)) {
            $erros[] = "Turno é obrigatório.";
        } elseif (!in_array($dto->turno, ['manha', 'tarde', 'noite'])) {
            $erros[] = "Turno inválido. Use: manha, tarde ou noite.";
        }

        if (empty($dto->capacidade_minima)) {
            $erros[] = "Capacidade mínima é obrigatória.";
        } elseif (!is_numeric($dto->capacidade_minima) || $dto->capacidade_minima < 1) {
            $erros[] = "Capacidade mínima deve ser um número positivo.";
        }

        if (empty($dto->capacidade_maxima)) {
            $erros[] = "Capacidade máxima é obrigatória.";
        } elseif (!is_numeric($dto->capacidade_maxima) || $dto->capacidade_maxima < 1) {
            $erros[] = "Capacidade máxima deve ser um número positivo.";
        }

        if (empty($dto->instrutor_id)) {
            $erros[] = "Instrutor é obrigatório.";
        } elseif (!is_numeric($dto->instrutor_id) || $dto->instrutor_id < 1) {
            $erros[] = "ID do instrutor inválido.";
        }

        if (empty($dto->modalidade_id)) {
            $erros[] = "Modalidade é obrigatória.";
        } elseif (!is_numeric($dto->modalidade_id) || $dto->modalidade_id < 1) {
            $erros[] = "ID da modalidade inválido.";
        }

        if (!empty($erros)) {
            throw new \Exception(implode(" | ", $erros));
        }
    }

    private function validateForUpdate(TurmaDTO $dto): void {
        $erros = [];

        if ($dto->nome !== null) {
            if (empty($dto->nome)) {
                $erros[] = "Nome não pode ser vazio.";
            } elseif (strlen($dto->nome) > 100) {
                $erros[] = "Nome não pode exceder 100 caracteres.";
            }
        }

        if ($dto->turno !== null) {
            if (!in_array($dto->turno, ['manha', 'tarde', 'noite'])) {
                $erros[] = "Turno inválido. Use: manha, tarde ou noite.";
            }
        }

        if ($dto->capacidade_minima !== null) {
            if (!is_numeric($dto->capacidade_minima) || $dto->capacidade_minima < 1) {
                $erros[] = "Capacidade mínima deve ser um número positivo.";
            }
        }

        if ($dto->capacidade_maxima !== null) {
            if (!is_numeric($dto->capacidade_maxima) || $dto->capacidade_maxima < 1) {
                $erros[] = "Capacidade máxima deve ser um número positivo.";
            }
        }

        if ($dto->instrutor_id !== null) {
            if (!is_numeric($dto->instrutor_id) || $dto->instrutor_id < 1) {
                $erros[] = "ID do instrutor inválido.";
            }
        }

        if ($dto->modalidade_id !== null) {
            if (!is_numeric($dto->modalidade_id) || $dto->modalidade_id < 1) {
                $erros[] = "ID da modalidade inválido.";
            }
        }

        if (!empty($erros)) {
            throw new \Exception(implode(" | ", $erros));
        }
    }

    private function validateCapacity(int $min, int $max): void {
        if ($min >= $max) {
            throw new \Exception("Capacidade mínima deve ser menor que a máxima.");
        }
    }
}
