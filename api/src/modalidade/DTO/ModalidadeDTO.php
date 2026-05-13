<?php
namespace Modalidade\DTO;

use Core\DTO\BaseDTO;

class ModalidadeDTO extends BaseDTO {
    public ?int $id = null;
    public string $nome;
    public ?string $descricao = null;
    public bool $ativo = true;
}