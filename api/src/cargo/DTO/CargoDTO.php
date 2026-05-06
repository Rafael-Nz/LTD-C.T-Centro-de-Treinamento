<?php
namespace Cargo\DTO;

use Core\DTO\BaseDTO;

class CargoDTO extends BaseDTO
{
    public ?int $id = null;
    public string $nome;
    public ?string $descricao = null;
    public float $salario_base = 0.0;
    public bool $ativo = true;
}