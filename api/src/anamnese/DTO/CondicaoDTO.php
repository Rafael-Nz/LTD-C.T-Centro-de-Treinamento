<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class CondicaoDTO extends BaseDTO
{
    public string $pergunta_slug = '';
    public string $operator; // equals, greater_than, etc
    public mixed $valor;
}