<?php
namespace Local\DTO;

use Core\DTO\BaseDTO;

class LocalDTO extends BaseDTO
{
    public ?int    $id               = null;
    public string  $nome;
    public int     $capacidade_minima;
    public int     $capacidade_maxima;
    public ?string $equipamentos     = null;
    public bool    $ativo            = true;
}