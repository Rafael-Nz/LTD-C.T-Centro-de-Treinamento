<?php

namespace Financeiro\DTO;

use Core\DTO\BaseDTO;

class ContratoDTO extends FinanceiroDTO
{
    public ?int $id = null;
    public int $aluno_id;
    public ?int $plano_id = null;
    public string $data_inicio;
    public ?string $data_fim = null;
    public int $dia_vencimento;
    public float $valor_contratado = 0;
    public float $desconto = 0;
    public float $multa_percentual = 0;
    public float $juros_percentual = 0;
    public string $status = 'ativo';
    public ?string $observacoes = null;
    public array $itens = [];
    public string $periodicidade = 'avulso';
}
