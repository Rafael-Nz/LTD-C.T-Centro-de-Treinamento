<?php
namespace Aluno;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Aluno\DTO\AlunoDTO;

class AlunoRepository extends Repository implements DataTablesRepositoryInterface {

    public function countAll(): int {
        $result = $this->fetch("
            SELECT COUNT(*) as total
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
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
                a.data_matricula,
                a.codigo_matricula,
                e.cidade,
                e.bairro
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
        ";

        // SEARCH GLOBAL (DataTables)
        if (!empty($search)) {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }

        // FILTROS
        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusArray = explode(',', $filters['status']);
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));

            $where[] = "u.ativo IN ($placeholders)";

            foreach ($statusArray as $s) {
                $params[] = $s;
            }
        }

        // aplica WHERE
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // ordenação padrão
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
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
        ";

        if (!empty($search)) {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusArray = explode(',', $filters['status']);
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));

            $where[] = "u.ativo IN ($placeholders)";

            foreach ($statusArray as $s) {
                $params[] = $s;
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);

        return (int) ($result['total'] ?? 0);
    }

    public function findAll(): array {
        return $this->fetchAll("
            SELECT 
                u.id,
                u.nome,
                u.sobrenome,
                u.email,
                u.cpf,
                u.ativo,
                a.data_matricula,
                a.codigo_matricula,
                e.cidade,
                e.bairro,
                CONCAT(fu.nome, ' ', fu.sobrenome) AS cadastrado_por_nome
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
            LEFT JOIN funcionario f ON f.usuario_id = a.cadastrado_por
            LEFT JOIN usuario fu ON fu.id = f.usuario_id
            ORDER BY u.nome
        ");
    }

    public function findAlunoData(int $id): ?array {
        return $this->fetch("
            SELECT 
                usuario_id,
                data_matricula,
                codigo_matricula
            FROM aluno
            WHERE usuario_id = ?
        ", [$id]);
    }

    public function create(AlunoDTO $dto, int $usuarioId): int {
        try {
            // ALUNO
            $this->execute("
                INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
                VALUES (?, ?, ?, ?)
            ", [
                $usuarioId,
                $dto->data_matricula ?? date('Y-m-d'),
                $dto->cadastrado_por,
                $dto->codigo_matricula ?? null
            ]);

            return $usuarioId;

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(int $usuarioId, AlunoDTO $dto): void {
        try {
            $fields = [];
            $params = [];

            if (isset($dto->data_matricula)) {
                $fields[] = "data_matricula = ?";
                $params[] = $dto->data_matricula;
            }

            if (empty($fields)) return;

            $params[] = $usuarioId;

            $this->execute("
                UPDATE aluno SET " . implode(', ', $fields) . "
                WHERE usuario_id = ?
            ", $params);

        } catch (\Throwable $e) {
            throw $e;
        }
    }
}