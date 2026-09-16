<?php

namespace Financeiro\DTO;

use Core\DTO\BaseDTO;

class PagamentoDTO extends FinanceiroDTO
{
    public float $valor_pago = 0;
    public ?string $data_pagamento = null;
    public string $forma_pagamento;
    public ?string $transacao_id = null;
    public ?string $observacoes = null;
    public ?int $registrado_por = null;
    public string $chave_idempotencia = '';
}
