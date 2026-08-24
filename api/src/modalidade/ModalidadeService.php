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
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        return $this->transaction(function () use ($dto) {
            if ($this->repo->findByNome($dto->nome)) {
                throw new \RuntimeException('Ja existe uma modalidade com este nome.');
            }

            return $this->repo->create($dto);
        });
    }

    public function update(int $id, ModalidadeDTO $dto): void {
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        $this->transaction(function () use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) {
                throw new \RuntimeException('Modalidade nao encontrada.');
            }

            $duplicate = $this->repo->findByNome($dto->nome);
            if ($duplicate && (int) $duplicate['id'] !== $id) {
                throw new \RuntimeException('Ja existe outra modalidade com este nome.');
            }

            $this->repo->update($id, $dto);
        });
    }

    public function deactivate(int $id): void {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Modalidade nao encontrada.');
        }

        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Modalidade nao encontrada.');
        }

        $this->repo->reactivate($id);
    }

    private function rulesForSave(): array {
        return [
            'nome' => ['required', 'string', 'max_length:50'],
            'descricao' => ['nullable', 'string'],
        ];
    }

    private function messages(): array {
        return [
            'nome.required' => 'Nome da modalidade e obrigatorio.',
            'nome.max_length' => 'Nome da modalidade nao pode exceder 50 caracteres.',
        ];
    }

    private function attributes(): array {
        return [
            'nome' => 'Nome da modalidade',
            'descricao' => 'Descricao',
        ];
    }
}
