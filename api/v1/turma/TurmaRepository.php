<?php
namespace Turma;

use Core\Repository;

class TurmaRepository extends Repository {

    // -------------------------------------------------------------------------
    // Leitura
    // -------------------------------------------------------------------------

    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                t.id,
                t.nome,
                t.descricao,
                t.turno,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(f.nome, ' ', f.sobrenome) AS instrutor_nome,
                f.id AS instrutor_id,
                e.nome AS espaco_treino_nome,
                e.id AS espaco_treino_id,
                e.capacidade_maxima AS espaco_capacidade_maxima,
                e.equipamentos
            FROM turma t
            INNER JOIN funcionario f ON f.id = t.instrutor_id
            INNER JOIN espaco_treino e ON e.id = t.espaco_treino_id
            ORDER BY t.nome
        ");
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT
                t.id,
                t.nome,
                t.descricao,
                t.turno,
                t.capacidade_minima,
                t.capacidade_maxima,
                t.ativo,
                t.data_criacao,
                t.data_atualizacao,
                CONCAT(f.nome, ' ', f.sobrenome) AS instrutor_nome,
                f.id AS instrutor_id,
                f.email AS instrutor_email,
                f.registro_profissional,
                e.nome AS espaco_treino_nome,
                e.id AS espaco_treino_id,
                e.capacidade_maxima AS espaco_capacidade_maxima,
                e.capacidade_minima AS espaco_capacidade_minima,
                e.equipamentos
            FROM turma t
            INNER JOIN funcionario f ON f.id = t.instrutor_id
            INNER JOIN espaco_treino e ON e.id = t.espaco_treino_id
            WHERE t.id = ?
        ", [$id]);
    }

    public function findByNome(string $nome): ?array {
        return $this->fetch(
            "SELECT id FROM turma WHERE nome = ? LIMIT 1",
            [$nome]
        );
    }

    public function findAlunosTurma(int $turmaId): array {
        return $this->fetchAll("
            SELECT
                a.id,
                a.nome,
                a.sobrenome,
                a.cpf,
                a.email,
                a.data_matricula,
                COUNT(at.id) AS total_treinos
            FROM aluno a
            INNER JOIN aluno_treino at ON at.aluno_id = a.id
            INNER JOIN treino tr ON tr.id = at.treino_id
            WHERE tr.turma_id = ?
            GROUP BY a.id
            ORDER BY a.nome, a.sobrenome
        ", [$turmaId]);
    }

    public function findTreinosTurma(int $turmaId): array {
        return $this->fetchAll("
            SELECT
                t.id,
                t.data_horario_inicio,
                t.data_horario_termino,
                t.status_treino,
                t.data_criacao,
                COUNT(at.id) AS total_alunos
            FROM treino t
            LEFT JOIN aluno_treino at ON at.treino_id = t.id
            WHERE t.turma_id = ?
            GROUP BY t.id
            ORDER BY t.data_horario_inicio DESC
        ", [$turmaId]);
    }

    // -------------------------------------------------------------------------
    // Escrita
    // -------------------------------------------------------------------------

    public function insertTurma(array $data): int {
        $this->execute("
            INSERT INTO turma
                (nome, descricao, turno, capacidade_minima, capacidade_maxima, instrutor_id, espaco_treino_id, ativo)
            VALUES
                (:nome, :descricao, :turno, :capacidade_minima, :capacidade_maxima, :instrutor_id, :espaco_treino_id, :ativo)
        ", [
            ':nome'                 => $data['nome'],
            ':descricao'            => $data['descricao'] ?? null,
            ':turno'                => $data['turno'],
            ':capacidade_minima'    => $data['capacidade_minima'],
            ':capacidade_maxima'    => $data['capacidade_maxima'],
            ':instrutor_id'         => $data['instrutor_id'],
            ':espaco_treino_id'     => $data['espaco_treino_id'],
            ':ativo'                => $data['ativo'] ?? 1,
        ]);

        return (int) $this->lastInsertId();
    }

    public function updateTurma(int $id, array $data): bool {
        $updates = [];
        $params  = [];

        $fields = ['nome', 'descricao', 'turno', 'capacidade_minima', 'capacidade_maxima', 'instrutor_id', 'espaco_treino_id', 'ativo'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[':id'] = $id;
        $sql = "UPDATE turma SET " . implode(', ', $updates) . " WHERE id = :id";

        return $this->execute($sql, $params);
    }

    public function deleteTurma(int $id): bool {
        return $this->execute("DELETE FROM turma WHERE id = ?", [$id]);
    }

    public function existsTurma(int $id): bool {
        $result = $this->fetch("SELECT id FROM turma WHERE id = ?", [$id]);
        return $result !== null;
    }

    public function verificaInstrutorAtivo(int $instrutorId): bool {
        $result = $this->fetch(
            "SELECT id FROM funcionario WHERE id = ? AND ativo = TRUE AND cargo_id = (SELECT id FROM cargo WHERE nome = 'Instrutor')",
            [$instrutorId]
        );
        return $result !== null;
    }

    public function verificaEspacoAtivo(int $espacoId): bool {
        $result = $this->fetch(
            "SELECT id FROM espaco_treino WHERE id = ? AND ativo = TRUE",
            [$espacoId]
        );
        return $result !== null;
    }
}
