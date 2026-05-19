<?php
namespace Cargo;

use Cargo\DTO\CargoDTO;
use Core\Services\Service;

class CargoService extends Service {
    private CargoRepository $cargoRepo;

    public function __construct() {
        $this->cargoRepo = new CargoRepository();
    }

    public function create(CargoDTO $dto): int {
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        return $this->transaction(function () use ($dto) {
            if ($this->cargoRepo->findByNome($dto->nome)) {
                throw new \RuntimeException("Ja existe um cargo com este nome.");
            }

            return $this->cargoRepo->create($dto);
        });
    }

    public function update(int $id, CargoDTO $dto): void {
        $this->validateData($dto, $this->rulesForSave(), $this->messages(), $this->attributes());

        $this->transaction(function () use ($id, $dto) {
            $existing = $this->cargoRepo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Cargo nao encontrado.");
            }

            $cargoWithSameName = $this->cargoRepo->findByNome($dto->nome);
            if ($cargoWithSameName && $cargoWithSameName['id'] != $id) {
                throw new \RuntimeException("Ja existe outro cargo com este nome.");
            }

            $this->cargoRepo->update($id, $dto);
        });
    }

    public function deactivate(int $id): void {
        $this->cargoRepo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->cargoRepo->reactivate($id);
    }

    private function rulesForSave(): array {
        return [
            'nome' => ['required', 'string', 'max_length:100'],
        ];
    }

    private function messages(): array {
        return [
            'nome.required' => 'Nome do cargo e obrigatorio.',
            'nome.max_length' => 'Nome do cargo nao pode exceder 100 caracteres.',
        ];
    }

    private function attributes(): array {
        return [
            'nome' => 'Nome do cargo',
        ];
    }
}
