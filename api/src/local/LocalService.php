<?php
namespace Local;

use Core\Services\Service;
use Local\DTO\LocalDTO;

class LocalService extends Service {
    private LocalRepository $repo;

    public function __construct() {
        $this->repo = new LocalRepository();
    }

    public function create(LocalDTO $dto): int {
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        return $this->transaction(function () use ($dto) {
            if ($this->repo->findByNome($dto->nome)) {
                throw new \RuntimeException("Ja existe um local de treino com esse nome.");
            }

            return $this->repo->create($dto);
        });
    }

    public function update(int $id, LocalDTO $dto): void {
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        $this->transaction(function () use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Local de treino nao encontrado.");
            }

            $sameNome = $this->repo->findByNome($dto->nome);
            if ($sameNome && (int) $sameNome['id'] !== $id) {
                throw new \RuntimeException("Ja existe outro local com este nome.");
            }

            $this->repo->update($id, $dto);
        });
    }

    public function deactivate(int $id): void {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException("Local de treino nao encontrado.");
        }

        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            throw new \RuntimeException("Local de treino nao encontrado.");
        }

        $this->repo->reactivate($id);
    }

    private function rulesForSave(): array {
        return [
            'nome' => ['required', 'string', 'max_length:50'],
            'capacidade_minima' => ['required', 'numeric', 'min:1', 'less_than_field:capacidade_maxima'],
            'capacidade_maxima' => ['required', 'numeric', 'min:1', 'greater_than_field:capacidade_minima'],
        ];
    }

    private function messages(): array {
        return [
            'nome.required' => 'Nome do local e obrigatorio.',
            'nome.max_length' => 'Nome nao pode exceder 50 caracteres.',
            'capacidade_minima.min' => 'Capacidade minima deve ser maior que zero.',
            'capacidade_maxima.min' => 'Capacidade maxima deve ser maior que zero.',
            'capacidade_minima.less_than_field' => 'Capacidade minima deve ser menor que a maxima.',
            'capacidade_maxima.greater_than_field' => 'Capacidade maxima deve ser maior que a minima.',
        ];
    }

    private function attributes(): array {
        return [
            'nome' => 'Nome',
            'capacidade_minima' => 'Capacidade minima',
            'capacidade_maxima' => 'Capacidade maxima',
        ];
    }
}
