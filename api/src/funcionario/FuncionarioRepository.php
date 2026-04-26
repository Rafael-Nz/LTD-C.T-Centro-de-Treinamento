<?php
namespace Funcionario;

use Core\Repository;

class FuncionarioRepository extends Repository {

    public function countAll(): int {
        $result = $this->fetch("
            SELECT COUNT(*) as total
            FROM funcionario f
            INNER JOIN usuario u ON u.id = f.usuario_id
        ");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.sobrenome,
                u.email,
                u.cpf,
                u.ativo,
                c.nome as cargo_nome
            FROM funcionario f
            INNER JOIN usuario u ON u.id = f.usuario_id
            LEFT JOIN cargo c ON c.id = f.cargo_id
        ";

        if (!empty($search)) {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "u.ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY u.nome ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "
            SELECT COUNT(*) as total
            FROM funcionario f
            INNER JOIN usuario u ON u.id = f.usuario_id
        ";

        if (!empty($search)) {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "u.ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT 
                u.*,
                c.nome as cargo_nome,
                f.cargo_id,
                f.registro_profissional
            FROM funcionario f
            INNER JOIN usuario u ON u.id = f.usuario_id
            LEFT JOIN cargo c ON c.id = f.cargo_id
            WHERE u.id = ?
        ", [$id]);
    }
}
