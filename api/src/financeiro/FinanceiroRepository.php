<?php

namespace Financeiro;

use Core\Database\Repository;
use Financeiro\DTO\ContratoDTO;
use Financeiro\DTO\PagamentoDTO;
use Financeiro\DTO\PlanoDTO;
use Financeiro\DTO\ServicoDTO;

class FinanceiroRepository extends Repository
{
    public function bloquearCatalogo(string $table, int $id): ?array {
        if (!in_array($table, ['servico', 'plano'], true)) throw new \InvalidArgumentException('Catálogo inválido.');
        return $this->fetch("SELECT * FROM $table WHERE id = ? FOR UPDATE", [$id]);
    }

    public function salvarCatalogo(string $table, int $id, array $dados): void {
        if (!in_array($table, ['servico', 'plano'], true)) throw new \InvalidArgumentException('Catálogo inválido.');
        $permitidos = $table === 'servico' ? ['nome', 'descricao', 'tipo', 'valor_base', 'recorrente', 'ativo'] : ['nome', 'descricao', 'periodicidade', 'valor', 'ativo'];
        $dados = array_intersect_key($dados, array_flip($permitidos));
        $sets = implode(', ', array_map(fn ($campo) => "$campo = ?", array_keys($dados)));
        $this->execute("UPDATE $table SET $sets WHERE id = ?", [...array_values($dados), $id]);
    }

    public function salvarStatusContrato(int $id, string $status): void {
        $this->execute('UPDATE contrato SET status = ? WHERE id = ?', [$status, $id]);
    }
    public function catalogoPaginado(string $table, array $filters): array {
        if (!in_array($table, ['servico', 'plano'], true)) throw new \InvalidArgumentException('Catalogo invalido.');
        $category = $table === 'servico' ? 'tipo' : 'periodicidade';
        $params = []; $where = [];
        $search = trim((string) ($filters['search']['value'] ?? ''));
        if ($search !== '') { $where[] = '(nome LIKE ? OR descricao LIKE ?)'; $params = ['%' . $search . '%', '%' . $search . '%']; }
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $ativos = array_values(array_intersect(explode(',', (string) $filters['ativo']), ['0', '1']));
            if (count($ativos) === 1) {
                $where[] = 'ativo = ?';
                $params[] = (int) $ativos[0];
            }
        }
        if (!empty($filters['categoria'])) { $where[] = "$category = ?"; $params[] = (string) $filters['categoria']; }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $total = (int) $this->fetch("SELECT COUNT(*) AS total FROM $table")['total'];
        $filtered = (int) $this->fetch("SELECT COUNT(*) AS total FROM $table" . $clause, $params)['total'];
        $length = min(100, max(1, (int) ($filters['length'] ?? 10)));
        $start = max(0, (int) ($filters['start'] ?? 0));
        return ['recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'data' => $this->fetchAll("SELECT * FROM $table" . $clause . " ORDER BY nome, id LIMIT $length OFFSET $start", $params)];
    }
    public function verificarEstrutura(): void {
        $this->fetchAll('SELECT c.periodicidade, c.geracao_automatica, p.chave_idempotencia, p.estornado_em, b.motivo_cancelamento FROM contrato c, pagamento p, cobranca b LIMIT 0');
    }
    public function alunoExiste(int $alunoId): bool
    {
        return $this->fetch('SELECT 1 FROM aluno WHERE usuario_id = ? LIMIT 1', [$alunoId]) !== null;
    }

    public function servicos(bool $somenteAtivos = true): array
    {
        $sql = 'SELECT * FROM servico';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = TRUE';
        }
        return $this->fetchAll($sql . ' ORDER BY nome');
    }

    public function servico(int $id): ?array
    {
        return $this->fetch('SELECT * FROM servico WHERE id = ?', [$id]);
    }

    public function servicoPorNome(string $nome): ?array
    {
        return $this->fetch('SELECT id FROM servico WHERE nome = ?', [$nome]);
    }

    public function criarServico(ServicoDTO $dto): int
    {
        $this->execute('INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo) VALUES (?, ?, ?, ?, ?, ?)', [
            $dto->nome,
            $dto->descricao,
            $dto->tipo,
            $dto->valor_base,
            $dto->recorrente ? 1 : 0,
            $dto->ativo ? 1 : 0,
        ]);
        return (int) $this->lastInsertId();
    }

    public function atualizarServico(int $id, ServicoDTO $dto): bool
    {
        return $this->execute('UPDATE servico SET nome = ?, descricao = ?, tipo = ?, valor_base = ?, recorrente = ?, ativo = ? WHERE id = ?', [
            $dto->nome,
            $dto->descricao,
            $dto->tipo,
            $dto->valor_base,
            $dto->recorrente ? 1 : 0,
            $dto->ativo ? 1 : 0,
            $id,
        ]);
    }

    public function planos(bool $somenteAtivos = true): array
    {
        $sql = 'SELECT * FROM plano';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = TRUE';
        }
        return $this->fetchAll($sql . ' ORDER BY nome');
    }

    public function plano(int $id): ?array
    {
        $plano = $this->fetch('SELECT * FROM plano WHERE id = ?', [$id]);
        if ($plano) {
            $plano['itens'] = $this->fetchAll('SELECT i.*, s.nome AS servico_nome FROM plano_item i INNER JOIN servico s ON s.id = i.servico_id WHERE i.plano_id = ? ORDER BY s.nome', [$id]);
        }
        return $plano;
    }

    public function planoPorNome(string $nome): ?array
    {
        return $this->fetch('SELECT id FROM plano WHERE nome = ?', [$nome]);
    }

    public function criarPlano(PlanoDTO $dto): int
    {
        $this->execute('INSERT INTO plano (nome, descricao, periodicidade, valor, ativo) VALUES (?, ?, ?, ?, ?)', [
            $dto->nome,
            $dto->descricao,
            $dto->periodicidade,
            $dto->valor,
            $dto->ativo ? 1 : 0,
        ]);
        return (int) $this->lastInsertId();
    }

    public function adicionarItemPlano(int $planoId, int $servicoId, int $quantidade, float $valor): void
    {
        $this->execute('INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario) VALUES (?, ?, ?, ?)', [$planoId, $servicoId, $quantidade, $valor]);
    }

    public function limparItensPlano(int $planoId): void
    {
        $this->execute('DELETE FROM plano_item WHERE plano_id = ?', [$planoId]);
    }

    public function contrato(int $id): ?array
    {
        $contrato = $this->fetch('SELECT c.*, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome, p.nome AS plano_nome FROM contrato c INNER JOIN usuario u ON u.id = c.aluno_id LEFT JOIN plano p ON p.id = c.plano_id WHERE c.id = ?', [$id]);
        if ($contrato) {
            $contrato['itens'] = $this->fetchAll('SELECT i.*, s.nome AS servico_nome FROM contrato_item i LEFT JOIN servico s ON s.id = i.servico_id WHERE i.contrato_id = ? ORDER BY i.id', [$id]);
        }
        return $contrato;
    }

    public function contratos(?int $alunoId = null): array
    {
        $sql = 'SELECT c.*, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome, p.nome AS plano_nome FROM contrato c INNER JOIN usuario u ON u.id = c.aluno_id LEFT JOIN plano p ON p.id = c.plano_id';
        $params = [];
        if ($alunoId !== null) {
            $sql .= ' WHERE c.aluno_id = ?';
            $params[] = $alunoId;
        }
        return $this->fetchAll($sql . ' ORDER BY c.data_inicio DESC, c.id DESC', $params);
    }

    public function contratosPaginados(array $filters): array {
        $from = ' FROM contrato c INNER JOIN usuario u ON u.id = c.aluno_id LEFT JOIN plano p ON p.id = c.plano_id';
        $where = []; $params = [];
        $search = trim((string) ($filters['search']['value'] ?? ''));
        if ($search !== '') {
            $where[] = "(CONCAT(u.nome, ' ', u.sobrenome) LIKE ? OR p.nome LIKE ?)";
            $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%';
        }
        $statuses = array_values(array_intersect(explode(',', (string) ($filters['status'] ?? '')), ['ativo', 'pausado', 'encerrado', 'cancelado']));
        if ($statuses) {
            $where[] = 'c.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
            $params = array_merge($params, $statuses);
        }
        if (!empty($filters['plano_id'])) { $where[] = 'c.plano_id = ?'; $params[] = (int) $filters['plano_id']; }
        if (!empty($filters['aluno_id'])) { $where[] = 'c.aluno_id = ?'; $params[] = (int) $filters['aluno_id']; }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $total = (int) $this->fetch('SELECT COUNT(*) AS total' . $from)['total'];
        $filtered = (int) $this->fetch('SELECT COUNT(*) AS total' . $from . $clause, $params)['total'];
        $start = max(0, (int) ($filters['start'] ?? 0));
        $length = min(100, max(1, (int) ($filters['length'] ?? 10)));
        $data = $this->fetchAll("SELECT c.*, CONCAT(u.nome, ' ', u.sobrenome) AS aluno_nome, p.nome AS plano_nome"
            . $from . $clause . " ORDER BY c.data_inicio DESC, c.id DESC LIMIT $length OFFSET $start", $params);
        return ['recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data];
    }

    public function criarContrato(ContratoDTO $dto): int
    {
        $this->execute('INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes, periodicidade, geracao_automatica) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $dto->aluno_id,
            $dto->plano_id,
            $dto->data_inicio,
            $dto->data_fim,
            $dto->dia_vencimento,
            $dto->valor_contratado,
            $dto->desconto,
            $dto->multa_percentual,
            $dto->juros_percentual,
            $dto->status,
            $dto->observacoes,
            $dto->periodicidade,
            1,
        ]);
        return (int) $this->lastInsertId();
    }

    public function adicionarItemContrato(int $contratoId, array $item): void
    {
        $this->execute('INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto) VALUES (?, ?, ?, ?, ?, ?)', [
            $contratoId,
            $item['servico_id'] ?? null,
            $item['descricao'],
            $item['quantidade'] ?? 1,
            $item['valor_unitario'],
            $item['valor_desconto'] ?? 0,
        ]);
    }

    public function cobrancasPaginadas(array $filters): array
    {
        $from = ' FROM cobranca c INNER JOIN usuario u ON u.id = c.aluno_id';
        $where = [];
        $params = [];
        $search = trim((string) ($filters['search']['value'] ?? ''));
        if ($search !== '') {
            $where[] = '(CONCAT(u.nome, " ", u.sobrenome) LIKE ? OR c.descricao LIKE ? OR c.competencia LIKE ?)';
            $params = array_fill(0, 3, '%' . $search . '%');
        }
        $statuses = array_values(array_intersect(explode(',', (string) ($filters['status'] ?? '')), ['aberta', 'paga', 'vencida', 'cancelada']));
        if ($statuses) {
            $where[] = 'c.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
            $params = array_merge($params, $statuses);
        }
        if (!empty($filters['aluno_id'])) {
            $where[] = 'c.aluno_id = ?';
            $params[] = (int) $filters['aluno_id'];
        }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $totalClause = !empty($filters['aluno_id']) ? ' WHERE c.aluno_id = ?' : '';
        $totalParams = !empty($filters['aluno_id']) ? [(int) $filters['aluno_id']] : [];
        $total = (int) $this->fetch('SELECT COUNT(*) AS total' . $from . $totalClause, $totalParams)['total'];
        $filtered = (int) $this->fetch('SELECT COUNT(*) AS total' . $from . $clause, $params)['total'];
        $length = min(100, max(1, (int) ($filters['length'] ?? 10)));
        $start = max(0, (int) ($filters['start'] ?? 0));
        $rows = $this->fetchAll('SELECT c.*, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome,
            COALESCE((SELECT SUM(p.valor_pago) FROM pagamento p WHERE p.cobranca_id = c.id AND p.estornado_em IS NULL), 0) AS total_pago'
            . $from . $clause . " ORDER BY c.data_vencimento, c.id LIMIT $length OFFSET $start", $params);
        foreach ($rows as &$row) {
            $row['saldo'] = max(0, (float) $row['valor_final'] - (float) $row['total_pago']);
        }
        unset($row);
        return ['recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows];
    }

    public function cobrancas(?int $alunoId = null, ?string $status = null): array
    {
        $sql = 'SELECT c.*, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome,
            COALESCE(p.total_pago, 0) AS total_pago,
            GREATEST(0, c.valor_final - COALESCE(p.total_pago, 0)) AS saldo
            FROM cobranca c INNER JOIN usuario u ON u.id = c.aluno_id
            LEFT JOIN (SELECT cobranca_id, SUM(valor_pago) AS total_pago FROM pagamento WHERE estornado_em IS NULL GROUP BY cobranca_id) p ON p.cobranca_id = c.id';
        $where = [];
        $params = [];
        if ($alunoId !== null) {
            $where[] = 'c.aluno_id = ?';
            $params[] = $alunoId;
        }
        if ($status !== null && $status !== '') {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return $this->fetchAll($sql . ' ORDER BY c.data_vencimento DESC, c.id DESC', $params);
    }

    public function cobranca(int $id): ?array
    {
        return $this->fetch('SELECT c.*, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome, ct.multa_percentual, ct.juros_percentual FROM cobranca c INNER JOIN usuario u ON u.id = c.aluno_id LEFT JOIN contrato ct ON ct.id = c.contrato_id WHERE c.id = ?', [$id]);
    }

    public function criarCobranca(int $contratoId, int $alunoId, ?string $competencia, string $descricao, float $valorOriginal, float $desconto, string $vencimento): int
    {
        $valorFinal = FinanceiroValidation::decimal(FinanceiroValidation::centavos($valorOriginal) - FinanceiroValidation::centavos($desconto));
        $this->execute('INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, valor_final, data_vencimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$contratoId, $alunoId, $competencia, $descricao, $valorOriginal, $desconto, $valorFinal, $vencimento]);
        return (int) $this->lastInsertId();
    }

    public function totalPago(int $cobrancaId): string
    {
        $row = $this->fetch('SELECT COALESCE(SUM(valor_pago), 0) AS total FROM pagamento WHERE cobranca_id = ? AND estornado_em IS NULL', [$cobrancaId]);
        return (string) ($row['total'] ?? '0');
    }

    public function registrarPagamento(int $cobrancaId, PagamentoDTO $dto, string $hash): int
    {
        $this->execute('INSERT INTO pagamento (cobranca_id, valor_pago, data_pagamento, forma_pagamento, transacao_id, observacoes, registrado_por, chave_idempotencia, requisicao_hash) VALUES (?, ?, COALESCE(?, CURRENT_TIMESTAMP), ?, ?, ?, ?, ?, ?)', [$cobrancaId, FinanceiroValidation::decimal(FinanceiroValidation::centavos($dto->valor_pago)), $dto->data_pagamento, $dto->forma_pagamento, $dto->transacao_id, $dto->observacoes, $dto->registrado_por, $dto->chave_idempotencia, $hash]);
        return (int) $this->lastInsertId();
    }

    public function marcarCobranca(int $id, string $status): void
    {
        $this->execute('UPDATE cobranca SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function atualizarVencidas(?string $hoje = null): int
    {
        $hoje = $hoje ?? date('Y-m-d');
        $this->execute("UPDATE cobranca SET status = 'vencida' WHERE status = 'aberta' AND data_vencimento < ?", [$hoje]);
        $row = $this->fetch("SELECT ROW_COUNT() AS total");
        return (int) ($row['total'] ?? 0);
    }

    public function bloquearCobranca(int $id): ?array {
        return $this->fetch('SELECT * FROM cobranca WHERE id = ? FOR UPDATE', [$id]);
    }

    public function bloquearContrato(int $id): ?array {
        return $this->fetch('SELECT * FROM contrato WHERE id = ? FOR UPDATE', [$id]);
    }

    public function pagamentoPorChave(string $key): ?array {
        return $this->fetch('SELECT * FROM pagamento WHERE chave_idempotencia = ? FOR UPDATE', [$key]);
    }

    public function pagamentoPorTransacao(string $id): ?array {
        return $this->fetch('SELECT id FROM pagamento WHERE transacao_unica = ? LIMIT 1', [$id]);
    }

    public function pagamentos(int $cobrancaId): array {
        return $this->fetchAll('SELECT p.*, CONCAT(u.nome, " ", u.sobrenome) AS registrado_por_nome,
            CONCAT(e.nome, " ", e.sobrenome) AS estornado_por_nome
            FROM pagamento p LEFT JOIN usuario u ON u.id = p.registrado_por
            LEFT JOIN usuario e ON e.id = p.estornado_por
            WHERE p.cobranca_id = ? ORDER BY p.data_pagamento DESC, p.id DESC', [$cobrancaId]);
    }

    public function bloquearPagamento(int $id, int $cobrancaId): ?array {
        return $this->fetch('SELECT * FROM pagamento WHERE id = ? AND cobranca_id = ? FOR UPDATE', [$id, $cobrancaId]);
    }

    public function estornar(int $id, string $motivo, ?int $actor): void {
        $this->execute('UPDATE pagamento SET estornado_em = CURRENT_TIMESTAMP, estornado_por = ?, motivo_estorno = ? WHERE id = ? AND estornado_em IS NULL', [$actor, $motivo, $id]);
    }

    public function cancelar(int $id, string $motivo, ?int $actor): void {
        $this->execute("UPDATE cobranca SET status = 'cancelada', cancelada_em = CURRENT_TIMESTAMP, cancelada_por = ?, motivo_cancelamento = ? WHERE id = ?", [$actor, $motivo, $id]);
    }

    public function porCompetencia(int $contratoId, string $competencia): ?array {
        return $this->fetch('SELECT * FROM cobranca WHERE contrato_id = ? AND competencia = ?', [$contratoId, $competencia]);
    }

    public function contratosAutomaticos(): array {
        return $this->fetchAll("SELECT id FROM contrato WHERE status = 'ativo' ORDER BY id");
    }

    public function metricas(?string $competencia = null, ?int $alunoId = null): array
    {
        $where = [];
        $params = [];
        if ($competencia !== null && $competencia !== '' && $competencia !== 'all') {
            $where[] = 'c.competencia = ?';
            $params[] = $competencia;
        }
        if ($alunoId !== null) {
            $where[] = 'c.aluno_id = ?';
            $params[] = $alunoId;
        }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $sql = 'SELECT c.id, c.status, c.valor_final,
            COALESCE((SELECT SUM(p.valor_pago) FROM pagamento p WHERE p.cobranca_id = c.id AND p.estornado_em IS NULL), 0) AS total_pago
            FROM cobranca c' . $clause;

        $rows = $this->fetchAll($sql, $params);

        $totalAberto = 0.0;
        $totalVencido = 0.0;
        $totalPago = 0.0;
        $totalCancelado = 0.0;
        $qtdCobrancas = count($rows);
        $qtdPagas = 0;
        $qtdAbertas = 0;
        $qtdVencidas = 0;

        foreach ($rows as $row) {
            $saldo = max(0, (float) $row['valor_final'] - (float) $row['total_pago']);
            $totalPago += (float) $row['total_pago'];

            if ($row['status'] === 'aberta') {
                $totalAberto += $saldo;
                $qtdAbertas++;
            } elseif ($row['status'] === 'vencida') {
                $totalVencido += $saldo;
                $qtdVencidas++;
            } elseif ($row['status'] === 'paga') {
                $qtdPagas++;
            } elseif ($row['status'] === 'cancelada') {
                $totalCancelado += (float) $row['valor_final'];
            }
        }

        $totalPrevisto = $totalAberto + $totalVencido + $totalPago;
        $taxaInadimplencia = $totalPrevisto > 0 ? round(($totalVencido / $totalPrevisto) * 100, 1) : 0;

        return [
            'total_aberto' => $totalAberto,
            'total_vencido' => $totalVencido,
            'total_pago' => $totalPago,
            'total_cancelado' => $totalCancelado,
            'total_cobrancas' => $qtdCobrancas,
            'qtd_pagas' => $qtdPagas,
            'qtd_abertas' => $qtdAbertas,
            'qtd_vencidas' => $qtdVencidas,
            'taxa_inadimplencia' => $taxaInadimplencia,
        ];
    }

    public function competenciasDisponiveis(?int $alunoId = null): array
    {
        $sql = 'SELECT DISTINCT competencia FROM cobranca WHERE competencia IS NOT NULL AND competencia != ""';
        $params = [];
        if ($alunoId !== null) {
            $sql .= ' AND aluno_id = ?';
            $params[] = $alunoId;
        }
        $sql .= ' ORDER BY competencia DESC LIMIT 24';
        $rows = $this->fetchAll($sql, $params);
        return array_values(array_filter(array_column($rows, 'competencia')));
    }

    public function exportarCobrancas(?string $competencia = null, ?string $status = null, ?int $alunoId = null): array
    {
        $from = ' FROM cobranca c INNER JOIN usuario u ON u.id = c.aluno_id';
        $where = [];
        $params = [];
        if ($competencia !== null && $competencia !== '' && $competencia !== 'all') {
            $where[] = 'c.competencia = ?';
            $params[] = $competencia;
        }
        if ($status !== null && $status !== '' && $status !== 'all') {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        if ($alunoId !== null) {
            $where[] = 'c.aluno_id = ?';
            $params[] = $alunoId;
        }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql = 'SELECT c.id, CONCAT(u.nome, " ", u.sobrenome) AS aluno_nome,
            c.descricao, c.competencia, c.data_vencimento, c.status,
            c.valor_original, c.desconto, c.multa, c.juros, c.valor_final,
            COALESCE((SELECT SUM(p.valor_pago) FROM pagamento p WHERE p.cobranca_id = c.id AND p.estornado_em IS NULL), 0) AS total_pago'
            . $from . $clause . ' ORDER BY c.data_vencimento DESC, c.id DESC';
        $rows = $this->fetchAll($sql, $params);
        foreach ($rows as &$row) {
            $row['saldo'] = max(0, (float) $row['valor_final'] - (float) $row['total_pago']);
        }
        unset($row);
        return $rows;
    }

    public function aplicarEncargos(int $cobrancaId, float $multa, float $juros): void
    {
        $cobranca = $this->fetch('SELECT valor_original, desconto FROM cobranca WHERE id = ?', [$cobrancaId]);
        if (!$cobranca) return;
        $original = FinanceiroValidation::centavos($cobranca['valor_original']);
        $desconto = FinanceiroValidation::centavos($cobranca['desconto']);
        $multaCent = FinanceiroValidation::centavos($multa);
        $jurosCent = FinanceiroValidation::centavos($juros);
        $finalCent = $original - $desconto + $multaCent + $jurosCent;
        $valorFinal = FinanceiroValidation::decimal($finalCent);

        $this->execute('UPDATE cobranca SET multa = ?, juros = ?, valor_final = ? WHERE id = ?', [
            $multa, $juros, $valorFinal, $cobrancaId
        ]);
    }

    public function reciboPagamento(int $cobrancaId, int $pagamentoId): ?array
    {
        return $this->fetch('SELECT p.*,
            c.aluno_id, c.descricao AS cobranca_descricao, c.competencia, c.data_vencimento,
            c.valor_original, c.desconto, c.multa, c.juros, c.valor_final, c.status AS cobranca_status,
            u.nome AS aluno_nome, u.sobrenome AS aluno_sobrenome, u.cpf AS aluno_cpf, u.email AS aluno_email,
            CONCAT(op.nome, " ", op.sobrenome) AS registrado_por_nome
            FROM pagamento p
            INNER JOIN cobranca c ON c.id = p.cobranca_id
            INNER JOIN usuario u ON u.id = c.aluno_id
            LEFT JOIN usuario op ON op.id = p.registrado_por
            WHERE p.id = ? AND p.cobranca_id = ?', [$pagamentoId, $cobrancaId]);
    }
}
