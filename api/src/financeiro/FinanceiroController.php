<?php

namespace Financeiro;

use Core\Auth\Auth;
use Core\Http\Controller;
use Financeiro\DTO\ContratoDTO;
use Financeiro\DTO\PagamentoDTO;
use Financeiro\DTO\PlanoDTO;
use Financeiro\DTO\ServicoDTO;

class FinanceiroController extends Controller
{
    private FinanceiroRepository $repo;
    private FinanceiroService $service;

    public function __construct()
    {
        $this->repo = new FinanceiroRepository();
        try {
            $this->repo->verificarEstrutura();
        } catch (\PDOException $e) {
            error_log('[Financeiro] Verifique a migracao financeiro_seguranca.sql.');
            $this->error('O financeiro aguarda a atualizacao do banco pelo administrador.', 503);
        }
        $this->service = new FinanceiroService();
    }

    public function servicos(): void
    {
        if (isset($_GET['draw'])) {
            $this->datatable(['draw' => max(0, (int) $_GET['draw'])] + $this->repo->catalogoPaginado('servico', $_GET));
            return;
        }
        $this->json($this->repo->servicos(($_GET['ativos'] ?? 'true') !== 'false'));
    }

    public function criarServico(): void
    {
        try {
            $id = $this->service->criarServico(ServicoDTO::fromArray($this->body()));
            $this->json(['id' => $id], 201);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao cadastrar servico financeiro.', 500);
        }
    }

    public function atualizarServico(int $id): void
    {
        try {
            $this->service->atualizarServico($id, ServicoDTO::fromArray($this->body()));
            $this->json(['message' => 'Servico atualizado com sucesso.']);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao atualizar servico financeiro.', 500);
        }
    }

    public function planos(): void
    {
        if (isset($_GET['draw'])) {
            $this->datatable(['draw' => max(0, (int) $_GET['draw'])] + $this->repo->catalogoPaginado('plano', $_GET));
            return;
        }
        $this->json($this->repo->planos(($_GET['ativos'] ?? 'true') !== 'false'));
    }

    public function criarPlano(): void
    {
        try {
            $id = $this->service->criarPlano(PlanoDTO::fromArray($this->body()));
            $this->json(['id' => $id], 201);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao cadastrar plano financeiro.', 500);
        }
    }

    public function contratos(): void
    {
        $this->executarJit();
        if (Auth::user('tipo') === 'aluno') {
            $_GET['aluno_id'] = (string) Auth::id();
        }

        if (isset($_GET['draw'])) {
            $this->datatable(['draw' => max(0, (int) $_GET['draw'])] + $this->repo->contratosPaginados($_GET));
            return;
        }
        $this->json($this->repo->contratos(isset($_GET['aluno_id']) ? (int) $_GET['aluno_id'] : null));
    }

    public function contrato(int $id): void
    {
        $item = $this->repo->contrato($id);
        if (!$item) {
            $this->error('Contrato financeiro nao encontrado.', 404);
            return;
        }

        if (Auth::user('tipo') === 'aluno' && (int) ($item['aluno_id'] ?? 0) !== (int) Auth::id()) {
            $this->error('Acesso negado a este contrato.', 403);
            return;
        }

        $this->json($item);
    }

    public function criarContrato(): void
    {
        try {
            $id = $this->service->criarContrato(ContratoDTO::fromArray($this->body()));
            $this->json(['id' => $id], 201);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao cadastrar contrato financeiro.', 500);
        }
    }

    public function cobrancas(): void
    {
        $this->executarJit();
        if (Auth::user('tipo') === 'aluno') {
            $_GET['aluno_id'] = (string) Auth::id();
        }

        if (isset($_GET['draw'])) {
            $this->datatable(['draw' => max(0, (int) $_GET['draw'])] + $this->repo->cobrancasPaginadas($_GET));
            return;
        }
        $this->json($this->repo->cobrancas(isset($_GET['aluno_id']) ? (int) $_GET['aluno_id'] : null, $_GET['status'] ?? null));
    }

    public function cobranca(int $id): void
    {
        $item = $this->repo->cobranca($id);
        if (!$item) {
            $this->error('Cobranca financeira nao encontrada.', 404);
            return;
        }

        if (Auth::user('tipo') === 'aluno' && (int) ($item['aluno_id'] ?? 0) !== (int) Auth::id()) {
            $this->error('Acesso negado a esta cobranca.', 403);
            return;
        }

        if ($item['status'] === 'aberta' && $item['data_vencimento'] < date('Y-m-d')) {
            $this->service->atualizarVencidas();
            $item['status'] = 'vencida';
        }

        $item['total_pago'] = $this->repo->totalPago($id);
        $item['saldo'] = max(0, (float) $item['valor_final'] - (float) $item['total_pago']);
        $item['pagamentos'] = $this->repo->pagamentos($id);
        $this->json($item);
    }

    public function gerarCobranca(int $contratoId): void
    {
        try {
            $id = $this->service->gerarCobranca($contratoId, $this->body());
            $this->json(['id' => $id], 201);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao gerar cobranca.', 500);
        }
    }

    public function registrarPagamento(int $cobrancaId): void
    {
        try {
            $body = $this->body();
            $dto = PagamentoDTO::fromArray($body);
            $dto->registrado_por = Auth::id();
            $aplicarEncargos = !empty($body['aplicar_encargos']);
            $id = $this->service->registrarPagamento($cobrancaId, $dto, $aplicarEncargos);
            $this->json(['id' => $id, 'message' => 'Pagamento registrado com sucesso.'], 201);
        } catch (\PDOException $e) {
            error_log('[Financeiro] Falha no pagamento: ' . $e->getCode());
            $this->error('Nao foi possivel registrar. Consulte a cobranca e tente novamente com a mesma operacao.', 409);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao registrar pagamento.', 500);
        }
    }

    public function atualizarVencidas(): void
    {
        $this->operacao(fn () => ['atualizadas' => $this->service->atualizarVencidas()]);
    }

    public function metricas(): void
    {
        $this->executarJit();
        $alunoId = Auth::user('tipo') === 'aluno' ? (int) Auth::id() : null;
        $competencia = $_GET['competencia'] ?? null;
        $metricas = $this->service->metricas($competencia, $alunoId);
        $competencias = $this->service->competenciasDisponiveis($alunoId);
        $this->json([
            'metricas' => $metricas,
            'competencias' => $competencias,
            'competencia_atual' => $competencia ?: (in_array(date('Y-m'), $competencias, true) ? date('Y-m') : 'all'),
        ]);
    }

    public function exportar(): void
    {
        $alunoId = Auth::user('tipo') === 'aluno' ? (int) Auth::id() : null;
        $competencia = $_GET['competencia'] ?? null;
        $status = $_GET['status'] ?? null;
        $rows = $this->service->exportarCobrancas($competencia, $status, $alunoId);

        $filename = 'cobrancas_' . ($competencia ?: 'todas') . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Aluno', 'Descrição', 'Competência', 'Vencimento', 'Status', 'Valor Original (R$)', 'Desconto (R$)', 'Multa (R$)', 'Juros (R$)', 'Valor Final (R$)', 'Total Pago (R$)', 'Saldo (R$)'], ';');
        foreach ($rows as $r) {
            fputcsv($output, [
                $r['id'],
                $r['aluno_nome'],
                $r['descricao'],
                $r['competencia'] ?? '-',
                $r['data_vencimento'],
                ucfirst($r['status']),
                number_format((float) $r['valor_original'], 2, ',', '.'),
                number_format((float) $r['desconto'], 2, ',', '.'),
                number_format((float) ($r['multa'] ?? 0), 2, ',', '.'),
                number_format((float) ($r['juros'] ?? 0), 2, ',', '.'),
                number_format((float) $r['valor_final'], 2, ',', '.'),
                number_format((float) $r['total_pago'], 2, ',', '.'),
                number_format((float) $r['saldo'], 2, ',', '.'),
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function calcularEncargos(int $id): void
    {
        try {
            $cobranca = $this->repo->cobranca($id);
            if (!$cobranca) {
                $this->error('Cobrança não encontrada.', 404);
                return;
            }
            if (Auth::user('tipo') === 'aluno' && (int) ($cobranca['aluno_id'] ?? 0) !== (int) Auth::id()) {
                $this->error('Acesso negado a esta cobrança.', 403);
                return;
            }
            $dataPagamento = $_GET['data_pagamento'] ?? date('Y-m-d');
            $this->json($this->service->calcularEncargos($cobranca, $dataPagamento));
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao calcular encargos.', 500);
        }
    }

    public function recibo(int $id, int $pagamento_id): void
    {
        try {
            $recibo = $this->service->recibo($id, $pagamento_id);
            if (Auth::user('tipo') === 'aluno' && (int) ($recibo['aluno_id'] ?? 0) !== (int) Auth::id()) {
                $this->error('Acesso negado a este recibo.', 403);
                return;
            }
            $this->json($recibo);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log($e);
            $this->error('Erro ao gerar comprovante de pagamento.', 500);
        }
    }

    public function servico(int $id): void {
        $item = $this->repo->servico($id);
        if (!$item) { $this->error('Serviço não encontrado.', 404); return; }
        $this->json($item);
    }
    public function plano(int $id): void {
        $item = $this->repo->plano($id);
        if (!$item) { $this->error('Plano não encontrado.', 404); return; }
        $this->json($item);
    }
    public function atualizarPlano(int $id): void {
        $this->operacao(fn () => $this->service->editarCatalogo('plano', $id, PlanoDTO::fromArray($this->body())));
    }
    public function statusServico(int $id): void {
        $this->operacao(fn () => $this->service->statusCatalogo('servico', $id, $this->body()['ativo'] ?? null));
    }
    public function statusPlano(int $id): void {
        $this->operacao(fn () => $this->service->statusCatalogo('plano', $id, $this->body()['ativo'] ?? null));
    }
    public function statusContrato(int $id): void {
        $this->operacao(fn () => $this->service->statusContrato($id, $this->body()['status'] ?? null));
    }

    private function operacao(callable $callback): void {
        try {
            $result = $callback();
            $this->json($result ?? ['message' => 'Operacao concluida.']);
        } catch (\PDOException $e) {
            error_log('[Financeiro] Falha transacional: ' . $e->getCode());
            $this->error('Nao foi possivel concluir a operacao. Atualize e tente novamente.', 409);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[Financeiro] Falha: ' . get_class($e));
            $this->error('Erro ao processar a operacao financeira.', 500);
        }
    }

    public function cancelarCobranca(int $id): void {
        $this->operacao(fn () => $this->service->cancelarCobranca($id, (string) ($this->body()['justificativa'] ?? ''), Auth::id()));
    }

    public function estornarPagamento(int $id, int $pagamento_id): void {
        $this->operacao(fn () => $this->service->estornarPagamento($id, $pagamento_id, (string) ($this->body()['justificativa'] ?? ''), Auth::id()));
    }

    public function gerarAutomaticas(): void {
        $this->operacao(fn () => $this->service->gerarAutomaticas());
    }

    private function executarJit(): void {
        try {
            $this->service->processarJustInTime();
        } catch (\Throwable $e) {
            error_log('[Financeiro JIT] ' . $e->getMessage());
        }
    }

}
