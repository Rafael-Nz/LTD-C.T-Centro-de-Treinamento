<?php
namespace Relatorio;

use Core\Database\Repository;

class RelatorioRepository extends Repository
{
    public function metricas(): array
    {
        $row = $this->fetch("
            SELECT
                (SELECT COUNT(*) FROM aluno) AS total_alunos,
                (SELECT COUNT(*) FROM aluno a INNER JOIN usuario u ON u.id = a.usuario_id WHERE u.ativo = 1) AS alunos_ativos,
                (SELECT COUNT(*) FROM turma WHERE ativo = 1) AS turmas_ativas,
                (SELECT COUNT(*) FROM turma) AS turmas_total,
                (SELECT COUNT(*) FROM avaliacao_fisica WHERE MONTH(data_avaliacao) = MONTH(CURDATE()) AND YEAR(data_avaliacao) = YEAR(CURDATE())) AS avaliacoes_mes,
                (SELECT COUNT(*) FROM funcionario f INNER JOIN usuario u ON u.id = f.usuario_id WHERE u.ativo = 1) AS funcionarios_ativos,
                (SELECT COUNT(*) FROM presenca_treino p INNER JOIN treino_agenda ta ON ta.id = p.treino_id WHERE ta.data_hora_inicio >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND p.situacao = 'presente') AS presencas,
                (SELECT COUNT(*) FROM presenca_treino p INNER JOIN treino_agenda ta ON ta.id = p.treino_id WHERE ta.data_hora_inicio >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS presencas_total
        ");

        $row = $row ?: [];
        $total = (int) ($row['presencas_total'] ?? 0);
        $row['taxa_presenca'] = $total > 0 ? round(((int) $row['presencas'] / $total) * 100, 2) : 0;
        return $row;
    }

    public function agenda(string $start = '', string $end = ''): array
    {
        $where = [];
        $params = [];

        foreach ([['valor' => $start, 'operador' => '>='], ['valor' => $end, 'operador' => '<']] as $periodo) {
            $data = trim($periodo['valor']);
            if ($data === '') {
                continue;
            }

            $data = str_replace('T', ' ', $data);
            if (strtotime($data) === false) {
                continue;
            }

            $where[] = "ta.data_hora_inicio {$periodo['operador']} ?";
            $params[] = $data;
        }

        $sql = "SELECT ta.id, ta.data_hora_inicio, ta.data_hora_fim, ta.status,
                    tr.nome AS treino, tr.descricao AS treino_descricao, m.nome AS modalidade,
                    t.id AS turma_id, t.nome AS turma,
                    et.nome AS espaco,
                    CONCAT(u.nome, ' ', u.sobrenome) AS instrutor
                FROM treino_agenda ta
                INNER JOIN treino tr ON tr.id = ta.treino_id
                LEFT JOIN modalidade m ON m.id = tr.modalidade_id
                LEFT JOIN turma t ON t.id = ta.turma_id
                INNER JOIN espaco_treino et ON et.id = ta.espaco_id
                LEFT JOIN funcionario f ON f.usuario_id = ta.instrutor_id
                LEFT JOIN usuario u ON u.id = f.usuario_id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ta.data_hora_inicio ASC';
        return $this->fetchAll($sql, $params);
    }

    public function alunos(array $filters): array
    {
        $where = [];
        $params = [];
        $modalidadeSubquery = '';
        $modalidadeSubqueryParams = [];
        $this->addStatusFilter($where, $params, 'u.ativo', $filters['status'] ?? '');
        $this->addDateFilter($where, $params, 'a.data_matricula', $filters);
        if (!empty($filters['modalidade'])) {
            $where[] = 'EXISTS (SELECT 1 FROM aluno_turma at2 INNER JOIN treino_agenda ta2 ON ta2.turma_id = at2.turma_id INNER JOIN treino tr2 ON tr2.id = ta2.treino_id WHERE at2.aluno_id = a.usuario_id AND tr2.modalidade_id = ?)';
            $params[] = (int) $filters['modalidade'];
            $modalidadeSubquery = ' AND m2.id = ?';
            $modalidadeSubqueryParams[] = (int) $filters['modalidade'];
        }

        return $this->fetchAll("SELECT u.id, u.nome, u.sobrenome, u.cpf, u.email, u.ativo, a.data_matricula, a.codigo_matricula,
            COUNT(DISTINCT CASE WHEN at.ativo = 1 THEN at.turma_id END) AS total_turmas,
            (SELECT GROUP_CONCAT(DISTINCT m2.nome ORDER BY m2.nome SEPARATOR ', ')
                FROM aluno_turma at2 INNER JOIN treino_agenda ta2 ON ta2.turma_id = at2.turma_id
                INNER JOIN treino tr2 ON tr2.id = ta2.treino_id INNER JOIN modalidade m2 ON m2.id = tr2.modalidade_id
                WHERE at2.aluno_id = a.usuario_id AND at2.ativo = 1" . $modalidadeSubquery . ") AS modalidades
            FROM aluno a INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN aluno_turma at ON at.aluno_id = a.usuario_id
            " . $this->where($where) . "
            GROUP BY u.id, u.nome, u.sobrenome, u.cpf, u.email, u.ativo, a.data_matricula, a.codigo_matricula
            ORDER BY u.nome, u.sobrenome", array_merge($modalidadeSubqueryParams, $params));
    }

    public function presenca(array $filters): array
    {
        $where = ['p.aluno_id = ?'];
        $params = [(int) $filters['aluno']];
        $this->addDateFilter($where, $params, 'ta.data_hora_inicio', $filters);
        if (!empty($filters['modalidade'])) {
            $where[] = 'tr.modalidade_id = ?';
            $params[] = (int) $filters['modalidade'];
        }
        if (!empty($filters['turma'])) {
            $where[] = 'ta.turma_id = ?';
            $params[] = (int) $filters['turma'];
        }

        return $this->fetchAll("SELECT DATE(ta.data_hora_inicio) AS data_treino, t.nome AS turma,
                m.nome AS modalidade, CONCAT(u.nome, ' ', u.sobrenome) AS aluno, p.situacao, p.checkin_time
            FROM presenca_treino p INNER JOIN treino_agenda ta ON ta.id = p.treino_id
            INNER JOIN treino tr ON tr.id = ta.treino_id LEFT JOIN modalidade m ON m.id = tr.modalidade_id
            INNER JOIN aluno a ON a.usuario_id = p.aluno_id INNER JOIN usuario u ON u.id = a.usuario_id
            LEFT JOIN turma t ON t.id = ta.turma_id " . $this->where($where) . "
            ORDER BY ta.data_hora_inicio DESC, turma, aluno", $params);
    }

    public function avaliacoes(array $filters): array
    {
        $where = [];
        $params = [];
        $this->addDateFilter($where, $params, 'av.data_avaliacao', $filters);
        if (!empty($filters['aluno'])) {
            $where[] = 'av.aluno_id = ?';
            $params[] = (int) $filters['aluno'];
        }

        return $this->fetchAll("SELECT av.id, av.data_avaliacao, av.peso, av.altura, av.imc, av.percentual_gordura,
                av.percentual_musculo, av.gordura_visceral,
            (SELECT m2.nome FROM aluno_turma at2 INNER JOIN treino_agenda ta2 ON ta2.turma_id = at2.turma_id
                INNER JOIN treino tr2 ON tr2.id = ta2.treino_id INNER JOIN modalidade m2 ON m2.id = tr2.modalidade_id
                WHERE at2.aluno_id = av.aluno_id ORDER BY ta2.data_hora_inicio DESC LIMIT 1) AS modalidade,
                CONCAT(au.nome, ' ', au.sobrenome) AS aluno, CONCAT(fu.nome, ' ', fu.sobrenome) AS avaliador
            FROM avaliacao_fisica av INNER JOIN usuario au ON au.id = av.aluno_id
            INNER JOIN funcionario f ON f.usuario_id = av.avaliador_id INNER JOIN usuario fu ON fu.id = f.usuario_id
            " . $this->where($where) . " ORDER BY av.data_avaliacao ASC, av.id ASC", $params);
    }

    public function turmas(array $filters): array
    {
        $where = ['t.ativo = 1'];
        $params = [];
        if (!empty($filters['modalidade'])) {
            $where[] = 'm.id = ?';
            $params[] = (int) $filters['modalidade'];
        }

        return $this->fetchAll("SELECT t.id, t.nome, m.nome AS modalidade, t.capacidade_maxima,
                CONCAT(iu.nome, ' ', iu.sobrenome) AS instrutor,
                COUNT(CASE WHEN at.ativo = 1 THEN at.aluno_id END) AS alunos,
                ROUND(COUNT(CASE WHEN at.ativo = 1 THEN at.aluno_id END) / NULLIF(t.capacidade_maxima, 0) * 100, 2) AS ocupacao
            FROM turma t
            LEFT JOIN (SELECT turma_id, MIN(treino_id) AS treino_id FROM treino_agenda GROUP BY turma_id) agenda ON agenda.turma_id = t.id
            LEFT JOIN treino tr ON tr.id = agenda.treino_id
            LEFT JOIN modalidade m ON m.id = tr.modalidade_id
            LEFT JOIN funcionario f ON f.usuario_id = t.instrutor_id LEFT JOIN usuario iu ON iu.id = f.usuario_id
            LEFT JOIN aluno_turma at ON at.turma_id = t.id
            " . $this->where($where) . " GROUP BY t.id, t.nome, m.nome, t.capacidade_maxima, iu.nome, iu.sobrenome ORDER BY t.nome", $params);
    }

    public function funcionarios(array $filters): array
    {
        $where = [];
        $params = [];
        $this->addStatusFilter($where, $params, 'u.ativo', $filters['status'] ?? '');
        if (!empty($filters['cargo'])) {
            $where[] = 'c.id = ?';
            $params[] = (int) $filters['cargo'];
        }
        return $this->fetchAll("SELECT u.nome, u.sobrenome, u.cpf, u.email, u.ativo, c.nome AS cargo,
                f.registro_profissional, COUNT(DISTINCT ta.id) AS treinos_realizados
            FROM funcionario f INNER JOIN usuario u ON u.id = f.usuario_id INNER JOIN cargo c ON c.id = f.cargo_id
            LEFT JOIN treino_agenda ta ON ta.instrutor_id = f.usuario_id AND ta.status = 'concluido'
            " . $this->where($where) . " GROUP BY f.usuario_id, u.nome, u.sobrenome, u.cpf, u.email, u.ativo, c.nome, f.registro_profissional ORDER BY u.nome", $params);
    }

    public function treinos(array $filters): array
    {
        $where = [];
        $params = [];
        $this->addDateFilter($where, $params, 'ta.data_hora_inicio', $filters);
        if (!empty($filters['turma'])) {
            $where[] = 'ta.turma_id = ?';
            $params[] = (int) $filters['turma'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['agendado', 'concluido', 'cancelado'], true)) {
            $where[] = 'ta.status = ?';
            $params[] = $filters['status'];
        }

        return $this->fetchAll("SELECT ta.data_hora_inicio, ta.data_hora_fim, ta.status, tr.nome AS treino,
                t.nome AS turma, et.nome AS espaco, CONCAT(u.nome, ' ', u.sobrenome) AS instrutor
            FROM treino_agenda ta INNER JOIN treino tr ON tr.id = ta.treino_id
            LEFT JOIN turma t ON t.id = ta.turma_id INNER JOIN espaco_treino et ON et.id = ta.espaco_id
            LEFT JOIN funcionario f ON f.usuario_id = ta.instrutor_id LEFT JOIN usuario u ON u.id = f.usuario_id
            " . $this->where($where) . " ORDER BY ta.data_hora_inicio DESC", $params);
    }

    public function contratos(array $filters): array
    {
        $tipoContrato = strtolower((string) ($filters['tipoContrato'] ?? 'plano'));
        if ($tipoContrato === 'servico' || $tipoContrato === 'servicos') {
            return $this->contratos_servicos($filters);
        }
        return $this->contratos_planos($filters);
    }

    public function contratos_planos(array $filters): array
    {
        $where = ['c.plano_id IS NOT NULL'];
        $params = [];

        if (!empty($filters['aluno'])) {
            $where[] = 'c.aluno_id = ?';
            $params[] = (int) $filters['aluno'];
        }

        if (!empty($filters['plano'])) {
            $where[] = 'c.plano_id = ?';
            $params[] = (int) $filters['plano'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['ativo', 'pausado', 'encerrado', 'cancelado'], true)) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }

        $this->addDateFilter($where, $params, 'c.data_inicio', $filters);

        return $this->fetchAll("SELECT c.id AS contrato_id, c.aluno_id,
                CONCAT(u.nome, ' ', u.sobrenome) AS aluno,
                u.cpf, u.email,
                p.nome AS plano,
                p.id AS plano_id,
                c.periodicidade,
                c.data_inicio, c.data_fim, c.dia_vencimento,
                c.valor_contratado, c.desconto,
                ROUND(c.valor_contratado - c.desconto, 2) AS valor_liquido,
                c.status
            FROM contrato c
            INNER JOIN usuario u ON u.id = c.aluno_id
            INNER JOIN plano p ON p.id = c.plano_id
            " . $this->where($where) . " ORDER BY c.data_inicio DESC, c.id DESC", $params);
    }

    public function contratos_servicos(array $filters): array
    {
        $where = ['(c.plano_id IS NULL OR (ci.id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM plano_item pi WHERE pi.plano_id = c.plano_id AND pi.servico_id = ci.servico_id)))'];
        $params = [];

        if (!empty($filters['aluno'])) {
            $where[] = 'c.aluno_id = ?';
            $params[] = (int) $filters['aluno'];
        }

        if (!empty($filters['servico'])) {
            $servicoId = (int) $filters['servico'];
            $where[] = '(ci.servico_id = ? OR s.id = ?)';
            $params[] = $servicoId;
            $params[] = $servicoId;
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['ativo', 'pausado', 'encerrado', 'cancelado'], true)) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }

        $this->addDateFilter($where, $params, 'c.data_inicio', $filters);

        return $this->fetchAll("SELECT 
                COALESCE(ci.id, 0) AS item_id,
                c.id AS contrato_id,
                c.aluno_id,
                CONCAT(u.nome, ' ', u.sobrenome) AS aluno,
                u.cpf,
                u.email,
                COALESCE(s.nome, ci.descricao, 'Serviço Avulso') AS servico,
                COALESCE(s.id, ci.servico_id) AS servico_id,
                COALESCE(ci.quantidade, 1) AS quantidade,
                COALESCE(ci.valor_unitario, c.valor_contratado) AS valor_unitario,
                COALESCE(ci.valor_desconto, c.desconto) AS valor_desconto,
                ROUND(
                    COALESCE(
                        (ci.quantidade * ci.valor_unitario) - ci.valor_desconto,
                        c.valor_contratado - c.desconto
                    ), 2
                ) AS valor_total,
                c.data_inicio,
                c.data_fim,
                c.dia_vencimento,
                c.status,
                COALESCE(p.nome, 'Contrato Avulso') AS origem_contrato
            FROM contrato c
            INNER JOIN usuario u ON u.id = c.aluno_id
            LEFT JOIN contrato_item ci ON ci.contrato_id = c.id
            LEFT JOIN servico s ON s.id = ci.servico_id
            LEFT JOIN plano p ON p.id = c.plano_id
            " . $this->where($where) . " ORDER BY c.data_inicio DESC, ci.id DESC, c.id DESC", $params);
    }

    private function where(array $where): string
    {
        return $where ? ' WHERE ' . implode(' AND ', $where) : '';
    }

    private function addStatusFilter(array &$where, array &$params, string $field, string $status): void
    {
        if ($status === 'ativo') {
            $where[] = "$field = 1";
        }
        if ($status === 'inativo') {
            $where[] = "$field = 0";
        }
    }

    private function addDateFilter(array &$where, array &$params, string $field, array $filters): void
    {
        foreach (['dataInicio' => '>=', 'dataFim' => '<='] as $key => $operator) {
            if (!empty($filters[$key])) {
                $date = $this->normalizeDate($filters[$key]);
                if ($date) {
                    $where[] = "$field $operator ?";
                    $params[] = $date . ($key === 'dataFim' && strlen($date) === 10 ? ' 23:59:59' : '');
                }
            }
        }
    }

    private function normalizeDate(string $date): ?string
    {
        $date = trim($date);
        $parsed = \DateTime::createFromFormat('d/m/Y', $date) ?: \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed ? $parsed->format('Y-m-d') : null;
    }
}
