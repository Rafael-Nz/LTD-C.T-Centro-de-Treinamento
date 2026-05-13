<?php
namespace Aluno;

use Aluno\DTO\AlunoDTO;
use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;

class AlunoRepository extends Repository implements DataTablesRepositoryInterface {
    public function countAll(): int {
        $result = $this->fetch("
            SELECT COUNT(*) AS total
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
                e.bairro,
                COUNT(DISTINCT at.turma_id) AS total_turmas
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
            LEFT JOIN aluno_turma at ON at.aluno_id = a.usuario_id AND at.ativo = TRUE
        ";

        if ($search !== '') {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusArray = explode(',', $filters['status']);
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));
            $where[] = "u.ativo IN ($placeholders)";
            foreach ($statusArray as $status) {
                $params[] = $status;
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY u.id, u.nome, u.sobrenome, u.email, u.cpf, u.ativo, a.data_matricula, a.codigo_matricula, e.cidade, e.bairro
            ORDER BY u.nome ASC
            LIMIT ? OFFSET ?
        ";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "
            SELECT COUNT(*) AS total
            FROM aluno a
            INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN endereco e ON e.id = u.endereco_id
        ";

        if ($search !== '') {
            $where[] = "(u.nome LIKE ? OR u.sobrenome LIKE ? OR u.email LIKE ? OR u.cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusArray = explode(',', $filters['status']);
            $placeholders = implode(',', array_fill(0, count($statusArray), '?'));
            $where[] = "u.ativo IN ($placeholders)";
            foreach ($statusArray as $status) {
                $params[] = $status;
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
        $aluno = $this->fetch("
            SELECT
                usuario_id,
                data_matricula,
                codigo_matricula
            FROM aluno
            WHERE usuario_id = ?
        ", [$id]);

        if (!$aluno) {
            return null;
        }

        $aluno['turmas'] = $this->findTurmasByAlunoId($id);
        return $aluno;
    }

    public function create(AlunoDTO $dto, int $usuarioId): int {
        $this->execute("
            INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
            VALUES (?, ?, ?, ?)
        ", [
            $usuarioId,
            $dto->data_matricula ?? date('Y-m-d'),
            $dto->cadastrado_por,
            $dto->codigo_matricula,
        ]);

        if ($dto->turma_ids !== null) {
            $this->syncTurmas($usuarioId, $dto->turma_ids);
        }

        return $usuarioId;
    }

    public function update(int $usuarioId, AlunoDTO $dto): void {
        $fields = [];
        $params = [];

        if ($dto->data_matricula !== null) {
            $fields[] = "data_matricula = ?";
            $params[] = $dto->data_matricula;
        }

        if (!empty($fields)) {
            $params[] = $usuarioId;
            $this->execute("
                UPDATE aluno SET " . implode(', ', $fields) . "
                WHERE usuario_id = ?
            ", $params);
        }

        if ($dto->turma_ids !== null) {
            $this->syncTurmas($usuarioId, $dto->turma_ids);
        }
    }

    public function syncTurmas(int $alunoId, array $turmaIds): void {
        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));

        $this->execute("DELETE FROM aluno_turma WHERE aluno_id = ?", [$alunoId]);

        foreach ($turmaIds as $turmaId) {
            if ($turmaId < 1) {
                continue;
            }

            $this->execute("
                INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
                VALUES (?, ?, CURDATE(), TRUE)
            ", [$alunoId, $turmaId]);
        }
    }

    public function findTurmasByAlunoId(int $alunoId): array {
        return $this->fetchAll("
            SELECT
                t.id,
                t.nome,
                at.data_inscricao,
                at.ativo
            FROM aluno_turma at
            INNER JOIN turma t ON t.id = at.turma_id
            WHERE at.aluno_id = ?
            ORDER BY t.nome ASC
        ", [$alunoId]);
    }

    public function findExistingTurmaIds(array $turmaIds): array {
        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        $turmaIds = array_filter($turmaIds, fn (int $id) => $id > 0);

        if (empty($turmaIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($turmaIds), '?'));
        $rows = $this->fetchAll("
            SELECT id
            FROM turma
            WHERE id IN ($placeholders)
        ", $turmaIds);

        return array_map(fn (array $row) => (int) $row['id'], $rows);
    }
}
