<?php

namespace Financeiro;

use Core\Services\Service;
use Financeiro\DTO\ContratoDTO;
use Financeiro\DTO\PagamentoDTO;
use Financeiro\DTO\PlanoDTO;
use Financeiro\DTO\ServicoDTO;

class FinanceiroService extends Service
{
    private const PERIODOS = ['mensal' => 1, 'trimestral' => 3, 'semestral' => 6, 'anual' => 12, 'avulso' => 0];
    private FinanceiroRepository $repo;

    public function __construct()
    {
        $this->repo = new FinanceiroRepository();
    }

    public function criarServico(ServicoDTO $dto): int
    {
        $this->validarServico($dto);
        if ($this->repo->servicoPorNome($dto->nome)) {
            throw new \RuntimeException('Ja existe um servico financeiro com este nome.');
        }
        return $this->repo->criarServico($dto);
    }

    public function atualizarServico(int $id, ServicoDTO $dto): void
    {
        $this->editarCatalogo('servico', $id, $dto);
    }

    public function editarCatalogo(string $table, int $id, ServicoDTO|PlanoDTO $dto): void {
        $this->transaction(function () use ($table, $id, $dto) {
            if (!$this->repo->bloquearCatalogo($table, $id)) throw new \InvalidArgumentException('Cadastro não encontrado.');
            $dto->nome = trim($dto->nome);
            if (mb_strlen($dto->nome) > 120) throw new \InvalidArgumentException('Nome deve ter até 120 caracteres.');
            if ($dto instanceof ServicoDTO) {
                $this->validarServico($dto);
                $same = $this->repo->servicoPorNome($dto->nome);
                $dados = ['tipo' => $dto->tipo, 'valor_base' => $dto->valor_base, 'recorrente' => (int) $dto->recorrente];
            } else {
                FinanceiroValidation::centavos($dto->valor, 'Valor');
                if ($dto->nome === '' || !array_key_exists($dto->periodicidade, self::PERIODOS)) throw new \InvalidArgumentException('Nome ou periodicidade inválidos.');
                $same = $this->repo->planoPorNome($dto->nome);
                $dados = ['periodicidade' => $dto->periodicidade, 'valor' => $dto->valor];
            }
            if ($same && (int) $same['id'] !== $id) throw new \InvalidArgumentException('Já existe outro cadastro com esse nome.');
            $this->repo->salvarCatalogo($table, $id, ['nome' => $dto->nome, 'descricao' => $dto->descricao] + $dados);
        });
    }

    public function statusCatalogo(string $table, int $id, mixed $ativo): void {
        if (!is_bool($ativo)) throw new \InvalidArgumentException('Status inválido.');
        $this->transaction(function () use ($table, $id, $ativo) {
            if (!$this->repo->bloquearCatalogo($table, $id)) throw new \InvalidArgumentException('Cadastro não encontrado.');
            $this->repo->salvarCatalogo($table, $id, ['ativo' => (int) $ativo]);
        });
    }

    public function statusContrato(int $id, mixed $status): void {
        if (!in_array($status, ['ativo', 'pausado', 'encerrado', 'cancelado'], true)) throw new \InvalidArgumentException('Status inválido.');
        $this->transaction(function () use ($id, $status) {
            $contrato = $this->repo->bloquearContrato($id);
            if (!$contrato) throw new \InvalidArgumentException('Contrato não encontrado.');
            if ($contrato['status'] === $status) return;
            if (in_array($contrato['status'], ['encerrado', 'cancelado'], true)) throw new \InvalidArgumentException('Contratos encerrados ou cancelados não podem ser reabertos. Cadastre um novo contrato.');
            if ($status === 'ativo' && !empty($contrato['data_fim']) && $contrato['data_fim'] < date('Y-m-d')) throw new \InvalidArgumentException('A vigência deste contrato já terminou.');
            $this->repo->salvarStatusContrato($id, $status);
        });
    }

    public function criarPlano(PlanoDTO $dto): int
    {
        FinanceiroValidation::centavos($dto->valor, 'Valor do plano');
        if (trim($dto->nome) === '' || $dto->valor < 0 || !in_array($dto->periodicidade, ['mensal', 'trimestral', 'semestral', 'anual', 'avulso'], true)) {
            throw new \InvalidArgumentException('Nome, valor e periodicidade do plano sao obrigatorios e validos.');
        }
        if ($this->repo->planoPorNome($dto->nome)) {
            throw new \RuntimeException('Ja existe um plano financeiro com este nome.');
        }
        return $this->transaction(function () use ($dto) {
            $id = $this->repo->criarPlano($dto);
            foreach ($dto->itens as $item) {
                if (!is_array($item)) throw new \InvalidArgumentException('Item do plano invalido.');
                $servicoId = (int) ($item['servico_id'] ?? 0);
                $servico = $this->repo->servico($servicoId);
                if (!$servico || !$servico['ativo']) {
                    throw new \InvalidArgumentException('Todo item do plano deve referenciar um servico ativo.');
                }
                $quantidade = (int) ($item['quantidade'] ?? 1);
                if (!is_array($item) || !preg_match('/^[1-9]\d{0,5}$/D', (string) ($item['quantidade'] ?? 1))) throw new \InvalidArgumentException('Quantidade invalida.');
                FinanceiroValidation::centavos($item['valor_unitario'] ?? $servico['valor_base'], 'Valor do item');
                $valor = (float) ($item['valor_unitario'] ?? $servico['valor_base']);
                if ($quantidade < 1 || $valor < 0) {
                    throw new \InvalidArgumentException('Quantidade e valor dos itens do plano sao invalidos.');
                }
                $this->repo->adicionarItemPlano($id, $servicoId, $quantidade, $valor);
            }
            return $id;
        });
    }

    public function criarContrato(ContratoDTO $dto): int
    {
        $inicio = FinanceiroValidation::data($dto->data_inicio ?? '', 'Data de inicio');
        if ($dto->data_fim !== null && FinanceiroValidation::data($dto->data_fim, 'Data de fim') < $inicio) throw new \InvalidArgumentException('O fim deve ser igual ou posterior ao inicio.');
        if (!in_array($dto->status, ['ativo', 'pausado', 'encerrado', 'cancelado'], true)) throw new \InvalidArgumentException('Status de contrato invalido.');
        foreach (['valor_contratado', 'desconto', 'multa_percentual', 'juros_percentual'] as $field) FinanceiroValidation::centavos($dto->$field, $field);
        if ($dto->multa_percentual > 100 || $dto->juros_percentual > 100) throw new \InvalidArgumentException('Percentuais devem estar entre zero e 100.');
        if (!$this->repo->alunoExiste($dto->aluno_id ?? 0)) {
            throw new \InvalidArgumentException('Aluno nao encontrado.');
        }
        if (($dto->dia_vencimento ?? 0) < 1 || $dto->dia_vencimento > 28) {
            throw new \InvalidArgumentException('Dados financeiros do contrato sao invalidos.');
        }
        if ($dto->plano_id !== null) {
            $plano = $this->repo->plano($dto->plano_id);
            if (!$plano || !$plano['ativo']) {
                throw new \InvalidArgumentException('Plano financeiro nao encontrado ou inativo.');
            }
            if ($dto->valor_contratado === 0.0) {
                $dto->valor_contratado = (float) $plano['valor'];
            }
            if (!$dto->itens) {
                $dto->itens = $plano['itens'];
            }
            $dto->periodicidade = $plano['periodicidade'];
        }
        if (!isset(self::PERIODOS[$dto->periodicidade])) throw new \InvalidArgumentException('Periodicidade invalida.');
        if ($dto->desconto > $dto->valor_contratado) throw new \InvalidArgumentException('Desconto maior que o valor contratado.');
        return $this->transaction(function () use ($dto) {
            $id = $this->repo->criarContrato($dto);
            foreach ($dto->itens as $item) {
                if (!is_array($item) || !preg_match('/^[1-9]\d{0,5}$/D', (string) ($item['quantidade'] ?? 1))) throw new \InvalidArgumentException('Item ou quantidade invalida.');
                $unitario = FinanceiroValidation::centavos($item['valor_unitario'] ?? 0, 'Valor do item');
                $desconto = FinanceiroValidation::centavos($item['valor_desconto'] ?? 0, 'Desconto do item');
                if ($desconto > $unitario * (int) ($item['quantidade'] ?? 1)) throw new \InvalidArgumentException('Desconto do item maior que seu total.');
                if (!empty($item['servico_id']) && !$this->repo->servico((int) $item['servico_id'])) throw new \InvalidArgumentException('Servico do item nao encontrado.');
                $descricao = trim((string) ($item['descricao'] ?? $item['servico_nome'] ?? 'Servico contratado'));
                $this->repo->adicionarItemContrato($id, [
                    'servico_id' => $item['servico_id'] ?? null,
                    'descricao' => $descricao,
                    'quantidade' => (int) ($item['quantidade'] ?? 1),
                    'valor_unitario' => (float) ($item['valor_unitario'] ?? 0),
                    'valor_desconto' => (float) ($item['valor_desconto'] ?? 0),
                ]);
            }
            return $id;
        });
    }

    public function gerarCobranca(int $contratoId, array $dados): int
    {
        return $this->transaction(function () use ($contratoId, $dados) {
        $contrato = $this->repo->bloquearContrato($contratoId);
        if (!$contrato || $contrato['status'] !== 'ativo') {
            throw new \RuntimeException('Contrato ativo nao encontrado.');
        }
        $competencia = $dados['competencia'] ?? null;
        $vencimento = (string) ($dados['data_vencimento'] ?? '');
        if (!is_string($competencia) || !preg_match('/^\d{4}-\d{2}$/D', $competencia)) {
            throw new \InvalidArgumentException('Competencia deve estar em YYYY-MM e vencimento em YYYY-MM-DD.');
        }
        FinanceiroValidation::data($competencia . '-01', 'Competencia');
        FinanceiroValidation::data($vencimento, 'Vencimento');
        if (substr($vencimento, 0, 7) !== $competencia || $vencimento < $contrato['data_inicio'] || ($contrato['data_fim'] && $vencimento > $contrato['data_fim'])) throw new \InvalidArgumentException('Vencimento fora da competencia ou da vigencia do contrato.');
        $existente = $this->repo->porCompetencia($contratoId, $competencia);
        if ($existente) {
            if ($existente['data_vencimento'] !== $vencimento) throw new \InvalidArgumentException('Ja existe cobranca dessa competencia com outro vencimento.');
            return (int) $existente['id'];
        }
        $valor = (float) $contrato['valor_contratado'];
        $descricao = (string) ($dados['descricao'] ?? 'Cobranca ' . $competencia);
        if (mb_strlen($descricao) > 180) throw new \InvalidArgumentException('Descricao muito longa.');
        $id = $this->repo->criarCobranca($contratoId, (int) $contrato['aluno_id'], $competencia, $descricao, $valor, (float) $contrato['desconto'], $vencimento);
        $this->atualizarSaldo($id, $this->repo->bloquearCobranca($id));
        return $id;
        });
    }

    public function registrarPagamento(int $cobrancaId, PagamentoDTO $dto): int
    {
        $valor = FinanceiroValidation::centavos($dto->valor_pago, 'Pagamento');
        if ($valor < 1) throw new \InvalidArgumentException('Pagamento deve ser maior que zero.');
        if (!preg_match('/^[a-zA-Z0-9_-]{16,64}$/D', $dto->chave_idempotencia)) throw new \InvalidArgumentException('Identificacao da operacao ausente ou invalida. Atualize a pagina.');
        if ($dto->data_pagamento !== null && FinanceiroValidation::data($dto->data_pagamento, 'Data do pagamento', true) > new \DateTimeImmutable()) throw new \InvalidArgumentException('Pagamento nao pode ter data futura.');
        if (!in_array($dto->forma_pagamento ?? '', ['pix', 'dinheiro', 'cartao', 'boleto', 'transferencia', 'outro'], true)) throw new \InvalidArgumentException('Forma de pagamento invalida.');
        $dto->transacao_id = trim($dto->transacao_id ?? '') ?: null;
        if (strlen($dto->transacao_id ?? '') > 120) throw new \InvalidArgumentException('Identificacao bancaria muito longa.');
        $hash = hash('sha256', json_encode([$cobrancaId, $valor, $dto->forma_pagamento, $dto->data_pagamento, $dto->transacao_id, $dto->observacoes], JSON_THROW_ON_ERROR));
        return $this->transaction(function () use ($cobrancaId, $dto, $valor, $hash) {
        $cobranca = $this->repo->bloquearCobranca($cobrancaId);
        $existing = $this->repo->pagamentoPorChave($dto->chave_idempotencia);
        if ($existing) {
            if (!hash_equals($existing['requisicao_hash'], $hash)) throw new \InvalidArgumentException('Esta identificacao ja foi usada com outros dados.');
            return (int) $existing['id'];
        }
        if (!$cobranca || $cobranca['status'] === 'cancelada') {
            throw new \RuntimeException('Cobranca nao encontrada ou cancelada.');
        }
        $saldo = FinanceiroValidation::centavos($cobranca['valor_final']) - FinanceiroValidation::centavos($this->repo->totalPago($cobrancaId));
        if ($valor > $saldo) {
            throw new \InvalidArgumentException('O pagamento deve ser maior que zero e nao pode exceder o saldo da cobranca.');
        }
        if (!in_array($dto->forma_pagamento, ['pix', 'dinheiro', 'cartao', 'boleto', 'transferencia', 'outro'], true)) {
            throw new \InvalidArgumentException('Forma de pagamento invalida.');
        }
        if ($dto->transacao_id && $this->repo->pagamentoPorTransacao($dto->transacao_id)) throw new \InvalidArgumentException('Essa transacao bancaria ja foi registrada.');
        $id = $this->repo->registrarPagamento($cobrancaId, $dto, $hash);
        $this->atualizarSaldo($cobrancaId, $cobranca);
        return $id;
        });
    }

    private function atualizarSaldo(int $id, array $cobranca): void {
        $quitada = FinanceiroValidation::centavos($this->repo->totalPago($id)) >= FinanceiroValidation::centavos($cobranca['valor_final']);
        $this->repo->marcarCobranca($id, $quitada ? 'paga' : ($cobranca['data_vencimento'] < date('Y-m-d') ? 'vencida' : 'aberta'));
    }

    public function cancelarCobranca(int $id, string $motivo, ?int $actor): void {
        $motivo = FinanceiroValidation::motivo($motivo);
        $this->transaction(function () use ($id, $motivo, $actor) {
            $cobranca = $this->repo->bloquearCobranca($id);
            if (!$cobranca) throw new \InvalidArgumentException('Cobranca nao encontrada.');
            if ($cobranca['status'] === 'cancelada') return;
            if (FinanceiroValidation::centavos($this->repo->totalPago($id)) > 0) throw new \InvalidArgumentException('Estorne os pagamentos antes de cancelar a cobranca.');
            $this->repo->cancelar($id, $motivo, $actor);
        });
    }

    public function estornarPagamento(int $cobrancaId, int $pagamentoId, string $motivo, ?int $actor): void {
        $motivo = FinanceiroValidation::motivo($motivo);
        $this->transaction(function () use ($cobrancaId, $pagamentoId, $motivo, $actor) {
            $cobranca = $this->repo->bloquearCobranca($cobrancaId);
            $pagamento = $this->repo->bloquearPagamento($pagamentoId, $cobrancaId);
            if (!$cobranca || !$pagamento) throw new \InvalidArgumentException('Pagamento nao encontrado nessa cobranca.');
            if ($pagamento['estornado_em']) return;
            if ($cobranca['status'] === 'cancelada') throw new \InvalidArgumentException('Cobranca cancelada.');
            $this->repo->estornar($pagamentoId, $motivo, $actor);
            $this->atualizarSaldo($cobrancaId, $cobranca);
        });
    }

    public function atualizarVencidas(): int {
        return $this->transaction(fn () => $this->repo->atualizarVencidas());
    }

    public function gerarAutomaticas(?string $referencia = null): array {
        $limite = FinanceiroValidation::data($referencia ?? date('Y-m-d'))->modify('last day of this month');
        $geradas = 0;
        foreach ($this->repo->contratosAutomaticos() as $item) {
            $geradas += $this->transaction(function () use ($item, $limite) {
                $contrato = $this->repo->bloquearContrato((int) $item['id']);
                if (!$contrato || $contrato['status'] !== 'ativo') return 0;
                $count = 0;
                foreach (self::vencimentos($contrato, $limite) as $date) {
                    if ($this->repo->porCompetencia((int) $item['id'], substr($date, 0, 7))) continue;
                    $this->gerarCobranca((int) $item['id'], ['competencia' => substr($date, 0, 7), 'data_vencimento' => $date]);
                    $count++;
                }
                return $count;
            });
        }
        $vencidas = $this->atualizarVencidas();
        return ['geradas' => $geradas, 'vencidas' => $vencidas];
    }

    public static function vencimentos(array $contrato, \DateTimeImmutable $limite): array {
        $inicio = FinanceiroValidation::data($contrato['data_inicio']);
        $dia = (int) $contrato['dia_vencimento'];
        $periodo = self::PERIODOS[$contrato['periodicidade']] ?? null;
        if ($dia < 1 || $dia > 28 || $periodo === null) throw new \InvalidArgumentException('Contrato com periodicidade ou vencimento invalido.');
        $cursor = $inicio->modify('first day of this month')->setDate((int) $inicio->format('Y'), (int) $inicio->format('m'), $dia);
        if ($cursor < $inicio) $cursor = $cursor->modify('+1 month');
        $fim = empty($contrato['data_fim']) ? $limite : min($limite, FinanceiroValidation::data($contrato['data_fim']));
        $dates = [];
        while ($cursor <= $fim) {
            $dates[] = $cursor->format('Y-m-d');
            if ($periodo === 0) break;
            $cursor = $cursor->modify("+$periodo months");
        }
        return $dates;
    }

    private function validarServico(ServicoDTO $dto): void
    {
        FinanceiroValidation::centavos($dto->valor_base, 'Valor do servico');
        if (trim($dto->nome) === '' || $dto->valor_base < 0 || !in_array($dto->tipo, ['mensalidade', 'avaliacao', 'personal', 'taxa', 'outro'], true)) {
            throw new \InvalidArgumentException('Nome, tipo e valor do servico sao obrigatorios e validos.');
        }
    }
}
