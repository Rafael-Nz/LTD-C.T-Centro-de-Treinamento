<?php
namespace Cargo;

use Core\DataTablesRepositoryInterface;
use Core\Repository;
use Cargo\DTO\CargoDTO;

class CargoRepository extends Repository implements DataTablesRepositoryInterface{

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM cargo");
        return (int)($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];
        
        $sql = "SELECT 
                    c.id,
                    c.nome,
                    c.descricao,
                    c.salario_base,
                    c.ativo,
                    c.data_criacao,
                    c.data_atualizacao
                FROM cargo c";

        // Busca Global
        if (!empty($search)) {
            $where[] = "(c.nome LIKE ?)";
            $params[] = "%$search%";
        }

        // Filtro de Status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "c.ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY c.nome ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    // Contagem de registros filtrados (para DataTables)
    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "SELECT COUNT(*) as total FROM cargo c";
        
        // Busca Global
        if (!empty($search)) {
            $where[] = "(c.nome LIKE ?)";
            $params[] = "%$search%";
        }

        // Filtro de Status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "c.ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                c.id,
                c.nome,
                c.descricao,
                c.salario_base,
                c.ativo,
                c.data_criacao,
                c.data_atualizacao
            FROM cargo c
            ORDER BY c.nome
        ");
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT c.id, c.nome, c.descricao, c.salario_base, c.ativo, c.data_criacao, c.data_atualizacao
            FROM cargo c
            WHERE c.id = ?
        ", [$id]);
    }

    public function findByNome(string $nome): ?array {
        return $this->fetch("SELECT id FROM cargo WHERE nome = ?", [$nome]);
    }

    public function create(CargoDTO $dto): int {
        $this->execute("
            INSERT INTO cargo (nome, descricao, salario_base, ativo)
            VALUES (:nome, :descricao, :salario_base, :ativo)
        ", [
            ':nome'         => $dto->nome,
            ':descricao'    => $dto->descricao,
            ':salario_base' => $dto->salario_base,
            ':ativo'        => $dto->ativo ? 1 : 0,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, CargoDTO $dto): bool {
        return $this->execute("
            UPDATE cargo SET
                nome         = :nome,
                descricao    = :descricao,
                salario_base = :salario_base,
                ativo        = :ativo
            WHERE id = :id
        ", [
            ':nome'         => $dto->nome,
            ':descricao'    => $dto->descricao,
            ':salario_base' => $dto->salario_base,
            ':ativo'        => $dto->ativo ? 1 : 0,
            ':id'           => $id,
        ]);
    }

    // Desativa cargo (soft delete)

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE cargo SET ativo = 0 WHERE id = ?", [$id]);
    }
    public function reactivate(int $id): bool {
        return $this->execute("UPDATE cargo SET ativo = 1 WHERE id = ?", [$id] );
    }

}
