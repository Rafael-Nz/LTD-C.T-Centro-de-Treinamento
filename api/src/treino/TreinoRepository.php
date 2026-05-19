<?php
namespace Treino;

use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;
use Treino\DTO\TreinoAgendaDTO;
use Treino\DTO\TreinoDTO;

class TreinoRepository extends Repository implements DataTablesRepositoryInterface {
    public function findSimple(bool $somenteAtivos = true): array {
        $sql = "
            SELECT
                tr.id,
                tr.nome,
                tr.modalidade_id,
                m.nome AS modalidade_nome,
                tr.ativo
            FROM treino tr
            INNER JOIN modalidade m ON m.id = tr.modalidade_id
        ";

        if ($somenteAtivos) {
            $sql .= " WHERE tr.ativo = TRUE";
        }

        $sql .= " ORDER BY tr.nome ASC";
        return $this->fetchAll($sql);
    }

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) AS total FROM treino");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT
                tr.id,
                tr.nome,
                tr.modalidade_id,
                tr.descricao,
                tr.ativo,
                tr.data_criacao,
                tr.data_atualizacao,
                m.nome AS modalidade_nome
            FROM treino tr
            INNER JOIN modalidade m ON m.id = tr.modalidade_id
        ";

        if ($search !== '') {
            $where[] = "(tr.nome LIKE ? OR m.nome LIKE ? OR tr.descricao LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['modalidade_id']) && $filters['modalidade_id'] !== '') {
            $where[] = "tr.modalidade_id = ?";
            $params[] = $filters['modalidade_id'];
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "tr.ativo IN ($placeholders)";
            foreach ($ativoArray as $ativo) {
                $params[] = $ativo;
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY tr.nome ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "
            SELECT COUNT(*) AS total
            FROM treino tr
            INNER JOIN modalidade m ON m.id = tr.modalidade_id
        ";

        if ($search !== '') {
            $where[] = "(tr.nome LIKE ? OR m.nome LIKE ? OR tr.descricao LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['modalidade_id']) && $filters['modalidade_id'] !== '') {
            $where[] = "tr.modalidade_id = ?";
            $params[] = $filters['modalidade_id'];
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "tr.ativo IN ($placeholders)";
            foreach ($ativoArray as $ativo) {
                $params[] = $ativo;
            }
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
                tr.id,
                tr.nome,
                tr.modalidade_id,
                tr.descricao,
                tr.ativo,
                tr.data_criacao,
                tr.data_atualizacao,
                m.nome AS modalidade_nome
            FROM treino tr
            INNER JOIN modalidade m ON m.id = tr.modalidade_id
            WHERE tr.id = ?
        ", [$id]);
    }

    public function create(TreinoDTO $dto): int {
        $this->execute("
            INSERT INTO treino (nome, modalidade_id, descricao, ativo)
            VALUES (?, ?, ?, ?)
        ", [
            $dto->nome,
            $dto->modalidade_id,
            $dto->descricao,
            ($dto->ativo ?? true) ? 1 : 0,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, TreinoDTO $dto): bool {
        $updates = [];
        $params = [];

        if ($dto->nome !== null) {
            $updates[] = "nome = ?";
            $params[] = $dto->nome;
        }

        if ($dto->modalidade_id !== null) {
            $updates[] = "modalidade_id = ?";
            $params[] = $dto->modalidade_id;
        }

        if ($dto->descricao !== null) {
            $updates[] = "descricao = ?";
            $params[] = $dto->descricao;
        }

        if ($dto->ativo !== null) {
            $updates[] = "ativo = ?";
            $params[] = $dto->ativo ? 1 : 0;
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $id;

        return $this->execute(
            "UPDATE treino SET " . implode(', ', $updates) . " WHERE id = ?",
            $params
        );
    }

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE treino SET ativo = FALSE WHERE id = ?", [$id]);
    }

    public function reactivate(int $id): bool {
        return $this->execute("UPDATE treino SET ativo = TRUE WHERE id = ?", [$id]);
    }

    public function exists(int $id): bool {
        return $this->fetch("SELECT 1 FROM treino WHERE id = ? LIMIT 1", [$id]) !== null;
    }

    public function existsByNomeModalidade(string $nome, int $modalidadeId, ?int $excludeId = null): bool {
        $params = [$nome, $modalidadeId];
        $sql = "SELECT 1 FROM treino WHERE nome = ? AND modalidade_id = ?";

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";

        return $this->fetch($sql, $params) !== null;
    }

    public function verificaModalidadeAtiva(int $modalidadeId): bool {
        return $this->fetch(
            "SELECT 1 FROM modalidade WHERE id = ? AND ativo = TRUE LIMIT 1",
            [$modalidadeId]
        ) !== null;
    }

    public function verificaTreinoAtivo(int $treinoId): bool {
        return $this->fetch(
            "SELECT 1 FROM treino WHERE id = ? AND ativo = TRUE LIMIT 1",
            [$treinoId]
        ) !== null;
    }

    public function verificaTurmaExiste(int $turmaId): bool {
        return $this->fetch("SELECT 1 FROM turma WHERE id = ? LIMIT 1", [$turmaId]) !== null;
    }

    public function verificaEspacoAtivo(int $espacoId): bool {
        return $this->fetch(
            "SELECT 1 FROM espaco_treino WHERE id = ? AND ativo = TRUE LIMIT 1",
            [$espacoId]
        ) !== null;
    }

    public function verificaInstrutorAtivo(int $instrutorId): bool {
        return $this->fetch(
            "SELECT 1
             FROM funcionario f
             INNER JOIN usuario u ON u.id = f.usuario_id
             WHERE f.usuario_id = ? AND u.ativo = TRUE
             LIMIT 1",
            [$instrutorId]
        ) !== null;
    }

    public function verificaConflitoInstrutor(int $instrutorId, string $inicio, string $fim, ?int $ignorarId = null): bool {
        $params = [$instrutorId, $fim, $inicio];
        $sql = "
            SELECT 1 FROM treino_agenda
            WHERE instrutor_id = ?
              AND status != 'cancelado'
              AND data_hora_inicio < ?
              AND data_hora_fim > ?
        ";

        if ($ignorarId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignorarId;
        }

        return $this->fetch($sql, $params) !== null;
    }

    public function createAgenda(TreinoAgendaDTO $dto): int {
        $this->execute("
            INSERT INTO treino_agenda
                (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $dto->treino_id,
            $dto->turma_id,
            $dto->espaco_id,
            $dto->instrutor_id,
            $dto->data_hora_inicio,
            $dto->data_hora_fim,
            $dto->status ?? 'agendado',
            $dto->observacoes,
        ]);

        return (int) $this->lastInsertId();
    }

    public function verificaConflitoEspaco(int $espacoId, string $inicio, string $fim, ?int $ignorarId = null): bool {
        $params = [$espacoId, $fim, $inicio];
        $sql = "
            SELECT 1 FROM treino_agenda
            WHERE espaco_id = ?
              AND status != 'cancelado'
              AND data_hora_inicio < ?
              AND data_hora_fim > ?
        ";

        if ($ignorarId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignorarId;
        }

        return $this->fetch($sql, $params) !== null;
    }

    public function verificaConflitoTurma(int $turmaId, string $inicio, string $fim, ?int $ignorarId = null): bool {
        $params = [$turmaId, $fim, $inicio];
        $sql = "
            SELECT 1 FROM treino_agenda
            WHERE turma_id = ?
              AND status != 'cancelado'
              AND data_hora_inicio < ?
              AND data_hora_fim > ?
        ";

        if ($ignorarId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignorarId;
        }

        return $this->fetch($sql, $params) !== null;
    }
}
