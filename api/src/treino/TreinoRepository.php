<?php
namespace Treino;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Treino\DTO\TreinoDTO;

class TreinoRepository extends Repository implements DataTablesRepositoryInterface {
    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM treino_agenda");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where  = [];

        $sql = "
            SELECT
                ta.id,
                ta.data_hora_inicio,
                ta.data_hora_fim,
                ta.status,
                t.nome  AS turma_nome,
                t.turno AS turma_turno,
                e.nome  AS espaco_nome
            FROM treino_agenda ta
            INNER JOIN turma        t ON t.id = ta.turma_id
            INNER JOIN espaco_treino e ON e.id = ta.espaco_id 
        ";

        if (!empty($search)) {
            $where[] = "(t.nome LIKE ? OR e.nome LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "ta.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['turma_id']) && $filters['turma_id'] !== '') {
            $where[] = "ta.turma_id = ?";
            $params[] = $filters['turma_id'];
        }

        if (isset($filters['espaco_id']) && $filters['espaco_id'] !== '') {
            $where[] = "ta.espaco_id = ?";
            $params[] = $filters['espaco_id'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY ta.data_hora_inicio DESC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where  = [];

        $sql = "
            SELECT COUNT(*) as total
            FROM treino_agenda ta
            INNER JOIN turma        t ON t.id = ta.turma_id
            INNER JOIN espaco_treino e ON e.id = ta.espaco_id
        ";

        if (!empty($search)) {
            $where[] = "(t.nome LIKE ? OR e.nome LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "ta.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['turma_id']) && $filters['turma_id'] !== '') {
            $where[] = "ta.turma_id = ?";
            $params[] = $filters['turma_id'];
        }

        if (isset($filters['espaco_id']) && $filters['espaco_id'] !== '') { 
            $where[] = "ta.espaco_id = ?";
            $params[] = $filters['espaco_id'];
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
                ta.id,
                ta.turma_id,
                ta.espaco_id,
                ta.data_hora_inicio,
                ta.data_hora_fim,
                ta.status,
                t.nome  AS turma_nome,
                t.turno AS turma_turno,
                e.nome  AS espaco_nome,
                e.capacidade_maxima AS espaco_capacidade
            FROM treino_agenda ta
            INNER JOIN turma        t ON t.id = ta.turma_id
            INNER JOIN espaco_treino e ON e.id = ta.espaco_id   -- ← corrigido
            WHERE ta.id = ?
        ", [$id]);
    }

    public function create(TreinoDTO $dto): int {
        $this->execute("
            INSERT INTO treino_agenda (turma_id, espaco_id, data_hora_inicio, data_hora_fim, status)
            VALUES (?, ?, ?, ?, ?)
        ", [
            $dto->turma_id,
            $dto->espaco_id,
            $dto->data_hora_inicio,
            $dto->data_hora_fim,
            $dto->status,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, TreinoDTO $dto): bool {
        return $this->execute("
            UPDATE treino_agenda SET
                turma_id         = ?,
                espaco_id        = ?,
                data_hora_inicio = ?,
                data_hora_fim    = ?,
                status           = ?
            WHERE id = ?
        ", [
            $dto->turma_id,
            $dto->espaco_id,
            $dto->data_hora_inicio,
            $dto->data_hora_fim,
            $dto->status,
            $id,
        ]);
    }

    public function cancelar(int $id): bool {
        return $this->execute(
            "UPDATE treino_agenda SET status = 'cancelado' WHERE id = ?",
            [$id]
        );
    }

    public function verificaTurmaExiste(int $turmaId): bool {
        $result = $this->fetch("SELECT id FROM turma WHERE id = ?", [$turmaId]);
        return $result !== null;
    }

    public function verificaEspacoAtivo(int $espacoId): bool  {
        $result = $this->fetch(
            "SELECT id FROM espaco_treino WHERE id = ? AND ativo = 1", 
            [$espacoId]
        );
        return $result !== null;
    }

    public function verificaConflito(int $espacoId, string $inicio, string $fim, ?int $ignorarId = null): bool {
        $params = [$espacoId, $fim, $inicio];
        $sql = "
            SELECT id FROM treino_agenda
            WHERE espaco_id = ?
              AND status != 'cancelado'
              AND data_hora_inicio < ?
              AND data_hora_fim    > ?
        ";

        if ($ignorarId !== null) {
            $sql      .= " AND id != ?";
            $params[]  = $ignorarId;
        }

        return $this->fetch($sql, $params) !== null;
    }
}