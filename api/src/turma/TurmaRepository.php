<?php
namespace Turma;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Turma\DTO\TurmaDTO;

class TurmaRepository extends Repository implements DataTablesRepositoryInterface {

    // =========================================================================
    // LEITURA
    // =========================================================================

    /**
     * Retorna todas as turmas ativas com dados de instrutor e modalidade
     */
    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                t.id,
                t.nome,
                t.turno,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.email AS instrutor_email,
                u.id AS instrutor_id,
                m.id AS modalidade_id,
                m.nome AS modalidade_nome,
                m.descricao AS modalidade_descricao
            FROM turma t
            INNER JOIN usuario u ON u.id = t.instrutor_id
            INNER JOIN funcionario f ON f.usuario_id = u.id
            INNER JOIN modalidade m ON m.id = t.modalidade_id
            WHERE t.ativo = TRUE
            ORDER BY t.nome
        ");
    }

    /**
     * Retorna uma turma por ID com todos os dados relacionados
     */
    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT
                t.id,
                t.nome,
                t.turno,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.id AS instrutor_id,
                u.email AS instrutor_email,
                f.registro_profissional AS instrutor_registro_profissional,
                m.id AS modalidade_id,
                m.nome AS modalidade_nome,
                m.descricao AS modalidade_descricao
            FROM turma t
            INNER JOIN usuario u ON u.id = t.instrutor_id
            INNER JOIN funcionario f ON f.usuario_id = u.id
            INNER JOIN modalidade m ON m.id = t.modalidade_id
            WHERE t.id = ? AND t.ativo = TRUE
        ", [$id]);
    }

    /**
     * Verifica se existe turma com esse nome (exclui um ID específico)
     */
    public function existsByNomeExcluding(string $nome, int $excludeId): bool {
        $result = $this->fetch(
            "SELECT 1 FROM turma WHERE nome = ? AND id != ? LIMIT 1",
            [$nome, $excludeId]
        );
        return $result !== null;
    }

    /**
     * Verifica se uma turma existe por ID
     */
    public function exists(int $id): bool {
        $result = $this->fetch("SELECT 1 FROM turma WHERE id = ? LIMIT 1", [$id]);
        return $result !== null;
    }

    /**
     * Verifica se instrutor (usuário) existe e é um funcionário ativo
     */
    public function verificaInstrutorAtivo(int $instrutorId): bool {
        $result = $this->fetch(
            "SELECT u.id FROM usuario u
             INNER JOIN funcionario f ON f.usuario_id = u.id
             WHERE u.id = ? AND u.ativo = TRUE
             LIMIT 1",
            [$instrutorId]
        );
        return $result !== null;
    }

    /**
     * Verifica se modalidade existe e está ativa
     */
    public function verificaModalidadeAtiva(int $modalidadeId): bool {
        $result = $this->fetch(
            "SELECT 1 FROM modalidade WHERE id = ? AND ativo = TRUE LIMIT 1",
            [$modalidadeId]
        );
        return $result !== null;
    }

    // =========================================================================
    // ESCRITA
    // =========================================================================

    /**
     * Cria uma nova turma a partir de DTO
     */
    public function create(TurmaDTO $dto): int {
        $this->execute("
            INSERT INTO turma
                (nome, modalidade_id, instrutor_id, turno, capacidade_minima, capacidade_maxima, ativo)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)
        ", [
            $dto->nome,
            $dto->modalidade_id,
            $dto->instrutor_id,
            $dto->turno,
            $dto->capacidade_minima,
            $dto->capacidade_maxima,
            $dto->ativo ? 1 : 0,
        ]);

        return (int) $this->lastInsertId();
    }

    /**
     * Atualiza turma a partir de DTO (atualiza apenas campos não-null)
     */
    public function update(int $id, TurmaDTO $dto): bool {
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
        if ($dto->instrutor_id !== null) {
            $updates[] = "instrutor_id = ?";
            $params[] = $dto->instrutor_id;
        }
        if ($dto->turno !== null) {
            $updates[] = "turno = ?";
            $params[] = $dto->turno;
        }
        if ($dto->capacidade_minima !== null) {
            $updates[] = "capacidade_minima = ?";
            $params[] = $dto->capacidade_minima;
        }
        if ($dto->capacidade_maxima !== null) {
            $updates[] = "capacidade_maxima = ?";
            $params[] = $dto->capacidade_maxima;
        }
        if (isset($dto->ativo)) {
            $updates[] = "ativo = ?";
            $params[] = $dto->ativo ? 1 : 0;
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE turma SET " . implode(', ', $updates) . " WHERE id = ?";
        return $this->execute($sql, $params);
    }

    /**
     * Desativa turma (soft delete)
     */
    public function deactivate(int $id): bool {
        return $this->execute("UPDATE turma SET ativo = FALSE WHERE id = ?", [$id]);
    }

    /**
     * Reativa turma
     */
    public function reactivate(int $id): bool {
        return $this->execute("UPDATE turma SET ativo = TRUE WHERE id = ?", [$id]);
    }

    // =========================================================================
    // DATATABLES
    // =========================================================================

    /**
     * Conta total de turmas
     */
    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM turma t");
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Busca turmas paginadas com search e filtros para DataTables
     */
    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT
                t.id,
                t.nome,
                t.turno,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.email AS instrutor_email,
                m.nome AS modalidade_nome
            FROM turma t
            INNER JOIN usuario u ON u.id = t.instrutor_id
            INNER JOIN modalidade m ON m.id = t.modalidade_id
        ";

        if (!empty($search)) {
            $where[] = "(t.nome LIKE ? OR CONCAT(u.nome, ' ', u.sobrenome) LIKE ? OR m.nome LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['turno']) && $filters['turno'] !== '') {
            $where[] = "t.turno = ?";
            $params[] = $filters['turno'];
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "t.ativo IN ($placeholders)";
            foreach ($ativoArray as $a) {
                $params[] = $a;
            }
        }

        if (isset($filters['modalidade_id']) && $filters['modalidade_id'] !== '') {
            $where[] = "t.modalidade_id = ?";
            $params[] = $filters['modalidade_id'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.nome ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Conta turmas com filtros aplicados
     */
    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = "
            SELECT COUNT(*) as total
            FROM turma t
            INNER JOIN usuario u ON u.id = t.instrutor_id
            INNER JOIN modalidade m ON m.id = t.modalidade_id
        ";

        if (!empty($search)) {
            $where[] = "(t.nome LIKE ? OR CONCAT(u.nome, ' ', u.sobrenome) LIKE ? OR m.nome LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%");
        }

        if (isset($filters['turno']) && $filters['turno'] !== '') {
            $where[] = "t.turno = ?";
            $params[] = $filters['turno'];
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "t.ativo IN ($placeholders)";
            foreach ($ativoArray as $a) {
                $params[] = $a;
            }
        }

        if (isset($filters['modalidade_id']) && $filters['modalidade_id'] !== '') {
            $where[] = "t.modalidade_id = ?";
            $params[] = $filters['modalidade_id'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);
        return (int) ($result['total'] ?? 0);
    }
}
