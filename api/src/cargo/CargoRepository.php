<?php
namespace Cargo;

use Core\Repository;

class CargoRepository extends Repository {

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM cargo");
        return (int)($result['total'] ?? 0);
    }

    // Busca paginada com filtros
    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];
        
        $sql = "SELECT c.* FROM cargo c";

        // 1. Busca Global (Search Bar)
        if (!empty($search)) {
            $where[] = "(c.nome LIKE ? OR c.descricao LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        // 2. Filtros Específicos (Array de Filtros)
        if (isset($filters['status']) && $filters['status'] !== '') {
            // Transforma a string "1,0" em um array [1, 0]
            $statusArray = explode(',', $filters['status']);
            
            // Cria os placeholders (?, ?) de acordo com a quantidade de itens
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));
            
            $where[] = "c.ativo IN ($placeholders)";
            
            // Adiciona cada valor ao array de parâmetros
            foreach ($statusArray as $s) {
                $params[] = $s;
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY c.id ORDER BY c.nome ASC LIMIT $length OFFSET $start";
        return $this->fetchAll($sql, $params);
    }

    // Contagem de registros filtrados (para DataTables)
    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "SELECT COUNT(*) as total FROM cargo c";
        
        // Busca Global
        if (!empty($search)) {
            $where[] = "(c.nome LIKE ? OR c.descricao LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        // Filtros:
        // Status 
        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusArray = explode(',', $filters['status']);
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));
            $where[] = "c.ativo IN ($placeholders)";
            foreach ($statusArray as $s) {
                $params[] = $s;
            }
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

    public function insert(array $data): int {
        $this->execute("
            INSERT INTO cargo (nome, descricao, salario_base, ativo)
            VALUES (:nome, :descricao, :salario_base, :ativo)
        ", [
            ':nome'        => $data['nome'],
            ':descricao'   => $data['descricao']   ?? null,
            ':salario_base'=> $data['salario_base'] ?? 0,
            ':ativo'       => $data['ativo']        ?? 1,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        return $this->execute("
            UPDATE cargo SET
                nome         = :nome,
                descricao    = :descricao,
                salario_base = :salario_base,
                ativo        = :ativo
            WHERE id = :id
        ", [
            ':nome'        => $data['nome'],
            ':descricao'   => $data['descricao']   ?? null,
            ':salario_base'=> $data['salario_base'] ?? 0,
            ':ativo'       => $data['ativo']        ?? 1,
            ':id'          => $id,
        ]);
    }

    public function toggleAtivo(int $id, int $ativo): bool {
        return $this->execute("UPDATE cargo SET ativo = ? WHERE id = ?", [$ativo, $id]);
    }

    // Desativa cargo (soft delete)

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE cargo SET ativo = 0 WHERE id = ?", [$id]);
    }
    public function reactivate(int $id): bool {
        return $this->execute("UPDATE cargo SET ativo = 1 WHERE id = ?", [$id] );
    }

}
