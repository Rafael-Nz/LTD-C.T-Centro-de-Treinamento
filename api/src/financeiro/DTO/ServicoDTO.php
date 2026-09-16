<?php

namespace Financeiro\DTO;

use Core\DTO\BaseDTO;

class ServicoDTO extends FinanceiroDTO
{
    public ?int $id = null;
    public string $nome;
    public ?string $descricao = null;
    public string $tipo = 'mensalidade';
    public float $valor_base = 0;
    public bool $recorrente = true;
    public bool $ativo = true;
}
