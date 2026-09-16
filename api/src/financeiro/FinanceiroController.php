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
            $dto = PagamentoDTO::fromArray($this->body());
            $dto->registrado_por = Auth::id();
            $id = $this->service->registrarPagamento($cobrancaId, $dto);
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

}
