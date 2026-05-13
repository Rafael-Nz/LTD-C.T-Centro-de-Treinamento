<?php
namespace Modalidade;

use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;
use Modalidade\DTO\ModalidadeDTO;

class ModalidadeRepository extends Repository implements DataTablesRepositoryInterface {

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM modalidade");
        return (int)($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];
        
        $sql = "SELECT id, nome, descricao, ativo, data_criacao FROM modalidade";

        if (!empty($search)) {
            $where[] = "(nome LIKE ?)";
            $params[] = "%$search%";
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY nome ASC LIMIT $length OFFSET $start";

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "SELECT COUNT(*) as total FROM modalidade m";
        
        // Busca Global
        if (!empty($search)) {
            $where[] = "(m.nome LIKE ?)";
            $params[] = "%$search%";
        }

        // Filtro de Status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "m.ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);
        return (int)($result['total'] ?? 0);
    }
    
    public function findByNome(string $nome): ?array {
        return $this->fetch("SELECT id FROM modalidade WHERE nome = ?", [$nome]);
    }

    public function findById(int $id): ?array {
        return $this->fetch("SELECT * FROM modalidade WHERE id = ?", [$id]);
    }

    public function create(ModalidadeDTO $dto): int {
        $this->execute("
            INSERT INTO modalidade (nome, descricao, ativo)
            VALUES (?, ?, ?)
        ", [
            $dto->nome,
            $dto->descricao,
            $dto->ativo ? 1 : 0
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, ModalidadeDTO $dto): bool {
        return $this->execute("
            UPDATE modalidade SET nome = ?, descricao = ?, ativo = ? WHERE id = ?
        ", [
            $dto->nome,
            $dto->descricao,
            $dto->ativo ? 1 : 0,
            $id
        ]);
    }

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE modalidade SET ativo = 0 WHERE id = ?", [$id]);
    }
    public function reactivate(int $id): bool {
        return $this->execute("UPDATE modalidade SET ativo = 1 WHERE id = ?", [$id] );
    }
}
