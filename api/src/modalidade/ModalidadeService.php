<?php
namespace Modalidade;

use Core\Services\Service;
use Modalidade\DTO\ModalidadeDTO;

class ModalidadeService extends Service {
    private ModalidadeRepository $repo;

    public function __construct() {
        $this->repo = new ModalidadeRepository();
    }

    public function create(ModalidadeDTO $dto): int {
        return $this->transaction(function() use ($dto) {
            if ($this->repo->findByNome($dto->nome)) {
                throw new \RuntimeException("Já existe uma modalidade com este nome.");
            }
            return $this->repo->create($dto);
        });
    }

    public function update(int $id, ModalidadeDTO $dto): void {
        $this->transaction(function() use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) throw new \RuntimeException("Modalidade não encontrada.");

            $duplicate = $this->repo->findByNome($dto->nome);
            if ($duplicate && $duplicate['id'] != $id) {
                throw new \RuntimeException("Já existe outra modalidade com este nome.");
            }

            $this->repo->update($id, $dto);
        });
    }
    public function deactivate(int $id): void {
        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->repo->reactivate($id);
    }
}
