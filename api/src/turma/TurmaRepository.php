<?php
namespace Turma;

use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;
use Turma\DTO\TurmaDTO;

class TurmaRepository extends Repository implements DataTablesRepositoryInterface {
    public function findSimple(bool $somenteAtivas = true): array {
        $sql = "
            SELECT
                t.id,
                t.nome,
                t.ativo
            FROM turma t
        ";

        if ($somenteAtivas) {
            $sql .= " WHERE t.ativo = TRUE";
        }

        $sql .= " ORDER BY t.nome";
        $turmas = $this->fetchAll($sql);
        return $this->appendHorarioMetadataToTurmas($turmas);
    }

    public function findAll(): array {
        $turmas = $this->fetchAll("
            SELECT
                t.id,
                t.nome,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.email AS instrutor_email,
                u.id AS instrutor_id
            FROM turma t
            LEFT JOIN usuario u ON u.id = t.instrutor_id
            WHERE t.ativo = TRUE
            ORDER BY t.nome
        ");

        $turmas = $this->appendConfigHorariosToTurmas($turmas);
        $turmas = $this->appendHorarioMetadataToTurmas($turmas);
        return $this->appendAlunosResumoToTurmas($turmas);
    }

    public function findById(int $id): ?array {
        $turma = $this->fetch("
            SELECT
                t.id,
                t.nome,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.id AS instrutor_id,
                u.email AS instrutor_email,
                f.registro_profissional AS instrutor_registro_profissional
            FROM turma t
            LEFT JOIN usuario u ON u.id = t.instrutor_id
            LEFT JOIN funcionario f ON f.usuario_id = u.id
            WHERE t.id = ?
        ", [$id]);

        if (!$turma) {
            return null;
        }

        $turma['config_horarios'] = $this->findConfigHorariosByTurmaId($id);
        $turma['horarios_resumo'] = $this->buildHorarioResumo($turma['config_horarios']);
        $turma['alunos'] = $this->findAlunosByTurmaId($id);
        $turma['total_alunos'] = count($turma['alunos']);

        return $turma;
    }

    public function existsByNomeExcluding(string $nome, int $excludeId): bool {
        return $this->fetch(
            "SELECT 1 FROM turma WHERE nome = ? AND id != ? LIMIT 1",
            [$nome, $excludeId]
        ) !== null;
    }

    public function exists(int $id): bool {
        return $this->fetch("SELECT 1 FROM turma WHERE id = ? LIMIT 1", [$id]) !== null;
    }

    public function verificaInstrutorAtivo(int $instrutorId): bool {
        return $this->fetch(
            "SELECT u.id
             FROM usuario u
             INNER JOIN funcionario f ON f.usuario_id = u.id
             WHERE u.id = ? AND u.ativo = TRUE
             LIMIT 1",
            [$instrutorId]
        ) !== null;
    }

    public function create(TurmaDTO $dto): int {
        $this->execute("
            INSERT INTO turma
                (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
            VALUES
                (?, ?, ?, ?, ?)
        ", [
            $dto->nome,
            $dto->instrutor_id,
            $dto->capacidade_minima,
            $dto->capacidade_maxima,
            ($dto->ativo ?? true) ? 1 : 0,
        ]);

        $turmaId = (int) $this->lastInsertId();
        $this->syncConfigHorarios($turmaId, $dto->config_horarios ?? []);

        return $turmaId;
    }

    public function update(int $id, TurmaDTO $dto): bool {
        $updates = [];
        $params = [];

        if ($dto->nome !== null) {
            $updates[] = "nome = ?";
            $params[] = $dto->nome;
        }

        if ($dto->instrutor_id !== null) {
            $updates[] = "instrutor_id = ?";
            $params[] = $dto->instrutor_id;
        }

        if ($dto->capacidade_minima !== null) {
            $updates[] = "capacidade_minima = ?";
            $params[] = $dto->capacidade_minima;
        }

        if ($dto->capacidade_maxima !== null) {
            $updates[] = "capacidade_maxima = ?";
            $params[] = $dto->capacidade_maxima;
        }

        $updated = false;

        if (!empty($updates)) {
            $params[] = $id;
            $updated = $this->execute(
                "UPDATE turma SET " . implode(', ', $updates) . " WHERE id = ?",
                $params
            );
        }

        if ($dto->config_horarios !== null) {
            $this->syncConfigHorarios($id, $dto->config_horarios);
            return true;
        }

        return $updated;
    }

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE turma SET ativo = FALSE WHERE id = ?", [$id]);
    }

    public function reactivate(int $id): bool {
        return $this->execute("UPDATE turma SET ativo = TRUE WHERE id = ?", [$id]);
    }

    public function findConfigHorariosByTurmaId(int $turmaId): array {
        return $this->fetchAll("
            SELECT
                id,
                dia_semana,
                hora_inicio,
                hora_fim
            FROM turma_config_horario
            WHERE turma_id = ?
            ORDER BY FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'), hora_inicio
        ", [$turmaId]);
    }

    public function findTreinosByTurmaId(int $turmaId, ?array $turma = null): array {
        $treinos = $this->fetchAll("
            SELECT
                ta.id,
                ta.treino_id,
                ta.instrutor_id,
                ta.data_hora_inicio,
                ta.data_hora_fim,
                ta.status,
                ta.observacoes,
                tr.nome AS treino_nome,
                tr.modalidade_id,
                m.nome AS modalidade_nome,
                e.nome AS espaco_nome,
                CONCAT(iu.nome, ' ', iu.sobrenome) AS instrutor_nome
            FROM treino_agenda ta
            INNER JOIN treino tr ON tr.id = ta.treino_id
            INNER JOIN modalidade m ON m.id = tr.modalidade_id
            INNER JOIN espaco_treino e ON e.id = ta.espaco_id
            LEFT JOIN usuario iu ON iu.id = ta.instrutor_id
            WHERE ta.turma_id = ?
            ORDER BY ta.data_hora_inicio ASC
        ", [$turmaId]);

        if (empty($treinos)) {
            return [];
        }

        $treinoIds = array_column($treinos, 'id');
        $presencasPorTreino = $this->findPresencasByTreinoIds($treinoIds);
        $turma = $turma ?? $this->findById($turmaId);

        $instrutorPadrao = null;
        if ($turma && !empty($turma['instrutor_id'])) {
            $instrutorPadrao = [
                'id' => (int) $turma['instrutor_id'],
                'nome' => $turma['instrutor_nome'] ?? null,
            ];
        }

        foreach ($treinos as &$treino) {
            $treino['id'] = (int) $treino['id'];
            $treino['treino'] = [
                'id' => (int) $treino['treino_id'],
                'nome' => $treino['treino_nome'],
                'modalidade_id' => (int) $treino['modalidade_id'],
                'modalidade_nome' => $treino['modalidade_nome'],
            ];
            unset($treino['treino_id'], $treino['treino_nome'], $treino['modalidade_id'], $treino['modalidade_nome']);

            $treino['espaco'] = $treino['espaco_nome'];
            unset($treino['espaco_nome']);

            $treino['instrutor'] = !empty($treino['instrutor_id']) ? [
                'id' => (int) $treino['instrutor_id'],
                'nome' => $treino['instrutor_nome'] ?? null,
            ] : $instrutorPadrao;
            unset($treino['instrutor_id'], $treino['instrutor_nome']);

            $treino['presenca'] = $presencasPorTreino[$treino['id']] ?? [];
        }

        return $treinos;
    }

    public function findTreinoAgendaByIdAndTurmaId(int $treinoId, int $turmaId): ?array {
        return $this->fetch("
            SELECT
                id,
                turma_id,
                status
            FROM treino_agenda
            WHERE id = ?
              AND turma_id = ?
            LIMIT 1
        ", [$treinoId, $turmaId]);
    }

    public function cancelTreinoAgendaByIdAndTurmaId(int $treinoId, int $turmaId): void {
        $this->execute("
            UPDATE treino_agenda
            SET status = 'cancelado'
            WHERE id = ?
              AND turma_id = ?
              AND status != 'cancelado'
        ", [$treinoId, $turmaId]);
    }

    public function findAlunosByTurmaId(int $turmaId): array {
        return $this->fetchAll("
            SELECT
                a.usuario_id AS aluno_id,
                a.codigo_matricula,
                at.data_inscricao,
                at.ativo,
                CONCAT(u.nome, ' ', u.sobrenome) AS aluno_nome,
                u.email
            FROM aluno_turma at
            INNER JOIN aluno a ON a.usuario_id = at.aluno_id
            INNER JOIN usuario u ON u.id = a.usuario_id
            WHERE at.turma_id = ?
            ORDER BY u.nome ASC, u.sobrenome ASC
        ", [$turmaId]);
    }

    public function syncPresencasTreino(int $treinoId, array $presencas): void {
        $existentes = $this->fetchAll("
            SELECT
                aluno_id,
                checkin_time
            FROM presenca_treino
            WHERE treino_id = ?
        ", [$treinoId]);

        $checkinsExistentes = [];
        foreach ($existentes as $presenca) {
            $checkinsExistentes[(int) $presenca['aluno_id']] = $presenca['checkin_time'];
        }

        $this->execute("DELETE FROM presenca_treino WHERE treino_id = ?", [$treinoId]);

        foreach ($presencas as $presenca) {
            $alunoId = (int) ($presenca['aluno_id'] ?? 0);
            $situacao = $presenca['situacao'] ?? null;

            if ($alunoId < 1 || !$situacao) {
                continue;
            }

            $checkinTime = null;
            if ($situacao === 'presente') {
                $checkinTime = $checkinsExistentes[$alunoId] ?? date('Y-m-d H:i:s');
            }

            $this->execute("
                INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
                VALUES (?, ?, ?, ?)
            ", [$treinoId, $alunoId, $situacao, $checkinTime]);
        }
    }

    public function markTreinoAsConcluido(int $treinoId): void {
        $this->execute("
            UPDATE treino_agenda
            SET status = 'concluido'
            WHERE id = ?
              AND status != 'cancelado'
        ", [$treinoId]);
    }

    public function syncConfigHorarios(int $turmaId, array $configHorarios): void {
        $this->execute("DELETE FROM turma_config_horario WHERE turma_id = ?", [$turmaId]);

        foreach ($configHorarios as $horario) {
            $dados = is_object($horario) ? get_object_vars($horario) : $horario;

            $this->execute("
                INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
                VALUES (?, ?, ?, ?)
            ", [
                $turmaId,
                $dados['dia_semana'] ?? null,
                $dados['hora_inicio'] ?? null,
                $dados['hora_fim'] ?? null,
            ]);
        }
    }

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) AS total FROM turma t");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT
                t.id,
                t.nome,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                CONCAT(u.nome, ' ', u.sobrenome) AS instrutor_nome,
                u.email AS instrutor_email,
                COUNT(DISTINCT at.aluno_id) AS total_alunos
            FROM turma t
            LEFT JOIN usuario u ON u.id = t.instrutor_id
            LEFT JOIN aluno_turma at ON at.turma_id = t.id AND at.ativo = TRUE
        ";

        if ($search !== '') {
            $where[] = "(t.nome LIKE ? OR CONCAT(u.nome, ' ', u.sobrenome) LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "t.ativo IN ($placeholders)";
            foreach ($ativoArray as $ativo) {
                $params[] = $ativo;
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY t.id, t.nome, t.capacidade_minima, t.capacidade_maxima, t.ativo, t.data_criacao, u.nome, u.sobrenome, u.email
            ORDER BY t.nome ASC
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
            FROM turma t
            LEFT JOIN usuario u ON u.id = t.instrutor_id
        ";

        if ($search !== '') {
            $where[] = "(t.nome LIKE ? OR CONCAT(u.nome, ' ', u.sobrenome) LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativoArray = explode(',', $filters['ativo']);
            $placeholders = implode(',', array_fill(0, count($ativoArray), '?'));
            $where[] = "t.ativo IN ($placeholders)";
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

    private function findPresencasByTreinoIds(array $treinoIds): array {
        if (empty($treinoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($treinoIds), '?'));
        $rows = $this->fetchAll("
            SELECT
                pt.treino_id,
                pt.aluno_id,
                pt.situacao,
                pt.checkin_time,
                CONCAT(u.nome, ' ', u.sobrenome) AS aluno_nome
            FROM presenca_treino pt
            INNER JOIN usuario u ON u.id = pt.aluno_id
            WHERE pt.treino_id IN ($placeholders)
            ORDER BY pt.treino_id ASC, u.nome ASC, u.sobrenome ASC
        ", $treinoIds);

        $presencasPorTreino = [];
        foreach ($rows as $row) {
            $treinoId = (int) $row['treino_id'];
            $presencasPorTreino[$treinoId][] = [
                'aluno_id' => (int) $row['aluno_id'],
                'aluno_nome' => $row['aluno_nome'],
                'situacao' => $row['situacao'],
                'checkin_time' => $row['checkin_time'],
            ];
        }

        return $presencasPorTreino;
    }

    private function appendConfigHorariosToTurmas(array $turmas): array {
        if (empty($turmas)) {
            return $turmas;
        }

        $turmaIds = array_column($turmas, 'id');
        $placeholders = implode(',', array_fill(0, count($turmaIds), '?'));
        $horarios = $this->fetchAll("
            SELECT
                id,
                turma_id,
                dia_semana,
                hora_inicio,
                hora_fim
            FROM turma_config_horario
            WHERE turma_id IN ($placeholders)
            ORDER BY FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'), hora_inicio
        ", $turmaIds);

        $horariosPorTurma = [];
        foreach ($horarios as $horario) {
            $horariosPorTurma[$horario['turma_id']][] = [
                'id' => $horario['id'],
                'dia_semana' => $horario['dia_semana'],
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fim' => $horario['hora_fim'],
            ];
        }

        foreach ($turmas as &$turma) {
            $turma['config_horarios'] = $horariosPorTurma[$turma['id']] ?? [];
        }

        return $turmas;
    }

    private function appendHorarioMetadataToTurmas(array $turmas): array {
        if (empty($turmas)) {
            return $turmas;
        }

        foreach ($turmas as &$turma) {
            if (!isset($turma['config_horarios'])) {
                $turma['config_horarios'] = $this->findConfigHorariosByTurmaId((int) $turma['id']);
            }

            $turma['horarios_resumo'] = $this->buildHorarioResumo($turma['config_horarios']);
        }

        return $turmas;
    }

    private function buildHorarioResumo(array $configHorarios): string {
        if (empty($configHorarios)) {
            return 'Sem horarios definidos';
        }

        $dias = [
            'segunda' => 'Seg',
            'terca' => 'Ter',
            'quarta' => 'Qua',
            'quinta' => 'Qui',
            'sexta' => 'Sex',
            'sabado' => 'Sab',
            'domingo' => 'Dom',
        ];

        $parts = array_map(function (array $horario) use ($dias) {
            $dia = $dias[$horario['dia_semana'] ?? ''] ?? ($horario['dia_semana'] ?? '');
            $inicio = substr((string) ($horario['hora_inicio'] ?? ''), 0, 5);
            $fim = substr((string) ($horario['hora_fim'] ?? ''), 0, 5);
            return trim("{$dia} {$inicio}-{$fim}");
        }, $configHorarios);

        return implode(', ', array_filter($parts));
    }

    private function appendAlunosResumoToTurmas(array $turmas): array {
        if (empty($turmas)) {
            return $turmas;
        }

        $turmaIds = array_column($turmas, 'id');
        $placeholders = implode(',', array_fill(0, count($turmaIds), '?'));
        $rows = $this->fetchAll("
            SELECT
                turma_id,
                COUNT(*) AS total_alunos
            FROM aluno_turma
            WHERE ativo = TRUE
              AND turma_id IN ($placeholders)
            GROUP BY turma_id
        ", $turmaIds);

        $totais = [];
        foreach ($rows as $row) {
            $totais[(int) $row['turma_id']] = (int) $row['total_alunos'];
        }

        foreach ($turmas as &$turma) {
            $turma['total_alunos'] = $totais[(int) $turma['id']] ?? 0;
        }

        return $turmas;
    }
}
