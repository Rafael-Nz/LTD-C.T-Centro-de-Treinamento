<?php
namespace Cargo;

use Core\Service;
use Cargo\DTO\CargoDTO;
 
class CargoService extends Service {

    private CargoRepository $cargoRepo;
    
    public function __construct() {
        $this->cargoRepo = new CargoRepository();
    }

    public function create(CargoDTO $dto): int {
        $this->validate($dto);
        
        return $this->transaction(function() use ($dto) {
            // Verifica se já existe cargo com mesmo nome
            if ($this->cargoRepo->findByNome($dto->nome)) {
                throw new \RuntimeException("Já existe um cargo com este nome.");
            }

            return $this->cargoRepo->create($dto);
        });
    }

    public function update(int $id, CargoDTO $dto): void {
        $this->validate($dto);
        
        $this->transaction(function() use ($id, $dto) {
            // Verifica se o cargo existe
            $existing = $this->cargoRepo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Cargo não encontrado.");
            }

            // Verifica se o novo nome já existe em outro cargo
            $cargoWithSameName = $this->cargoRepo->findByNome($dto->nome);
            if ($cargoWithSameName && $cargoWithSameName['id'] != $id) {
                throw new \RuntimeException("Já existe outro cargo com este nome.");
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

    private function validate(array $data): void {
        if (empty($data['nome']) || trim($data['nome']) === '') {
            throw new \InvalidArgumentException("Nome do cargo é obrigatório.");
        }
        
        if (strlen($data['nome']) > 100) {
            throw new \InvalidArgumentException("Nome do cargo não pode exceder 100 caracteres.");
        }
    }
}