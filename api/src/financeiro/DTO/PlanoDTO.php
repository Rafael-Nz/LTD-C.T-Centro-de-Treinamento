<?php

namespace Financeiro\DTO;

use Core\DTO\BaseDTO;

class PlanoDTO extends FinanceiroDTO
{
    public ?int $id = null;
    public string $nome;
    public ?string $descricao = null;
    public string $periodicidade = 'mensal';
    public float $valor = 0;
    public bool $ativo = true;
    public array $itens = [];
}
