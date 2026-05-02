<?php
namespace Funcionario;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Funcionario\DTO\FuncionarioDTO;
class FuncionarioRepository extends Repository implements DataTablesRepositoryInterface {

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
                e.*,
                c.nome as cargo_nome,
                f.cargo_id,
                f.registro_profissional
            FROM funcionario f
            INNER JOIN usuario u ON u.id = f.usuario_id
            LEFT JOIN cargo c ON c.id = f.cargo_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
            WHERE u.id = ?
        ", [$id]);
    }
    public function findFuncionarioData(int $id): ?array {
        return $this->fetch("
            SELECT 
                cargo_id,
                registro_profissional,
                observacoes
            FROM funcionario
            WHERE usuario_id = ?
        ", [$id]);
    }

    public function create(FuncionarioDTO $dto, int $usuarioId): int {
        $this->execute("
            INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
            VALUES (?, ?, ?, ?)
        ", [
            $usuarioId,
            $dto->cargo_id,
            $dto->registro_profissional ?? null,
            $dto->observacoes ?? null
        ]);

        return $usuarioId;
    }

    public function update(int $usuarioId, FuncionarioDTO $dto): void {
        $fields = [];
        $params = [];

        if (isset($dto->cargo_id)) {
            $fields[] = "cargo_id = ?";
            $params[] = $dto->cargo_id;
        }
        if (isset($dto->registro_profissional)) {
            $fields[] = "registro_profissional = ?";
            $params[] = $dto->registro_profissional;
        }
        if (isset($dto->observacoes)) {
            $fields[] = "observacoes = ?";
            $params[] = $dto->observacoes;
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $usuarioId;

        $this->execute("
            UPDATE funcionario SET " . implode(', ', $fields) . "
            WHERE usuario_id = ?
        ", $params);
    }
}
