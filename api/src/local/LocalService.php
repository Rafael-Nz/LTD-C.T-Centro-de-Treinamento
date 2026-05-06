<?php
namespace Local;

use Core\Service;
use Local\DTO\LocalDTO;  
class LocalService extends Service
{
    private LocalRepository $repo;

    public function __construct()
    {
        $this->repo = new LocalRepository();
    }

    public function create(LocalDTO $dto): int
    {
        $this->validate($dto);

        return $this->transaction(function () use ($dto) {
            if ($this->repo->findByNome($dto->nome)) {
                throw new \RuntimeException("Já existe um local de treino com esse nome.");
            }

            return $this->repo->create($dto);
        });
    }

    public function update(int $id, LocalDTO $dto): void
    {
        $this->validate($dto);

        $this->transaction(function () use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Local de treino não encontrado.");
            }

            $sameNome = $this->repo->findByNome($dto->nome);
            if ($sameNome && (int) $sameNome['id'] !== $id) {
                throw new \RuntimeException("Já existe outro local com este nome.");
            }

            $this->repo->update($id, $dto);
        });
    }

    public function deactivate(int $id): void
    {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException("Local de treino não encontrado.");
        }

        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void
    {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException("Local de treino não encontrado.");
        }

        $this->repo->reactivate($id);
    }

    private function validate(LocalDTO $dto): void
    {
        if (empty($dto->nome) || trim($dto->nome) === '') {
            throw new \InvalidArgumentException("Nome do local é obrigatório.");
        }

        if (strlen($dto->nome) > 50) {
            throw new \InvalidArgumentException("Nome não pode exceder 50 caracteres.");
        }

        if (empty($dto->capacidade_minima) || $dto->capacidade_minima < 1) {
            throw new \InvalidArgumentException("Capacidade mínima deve ser maior que zero.");
        }

        if (empty($dto->capacidade_maxima) || $dto->capacidade_maxima < 1) {
            throw new \InvalidArgumentException("Capacidade máxima deve ser maior que zero.");
        }

        if ($dto->capacidade_minima >= $dto->capacidade_maxima) {
            throw new \InvalidArgumentException("Capacidade mínima deve ser menor que a máxima.");
        }
    }
}