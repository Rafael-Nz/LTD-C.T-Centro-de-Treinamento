<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class OpcaoDTO extends BaseDTO
{
    public int $id;
    public int $pergunta_id;

    public string $label;
    public string $valor;

    public int $ordem = 0;

    public ?array $config = null; // JSON
}