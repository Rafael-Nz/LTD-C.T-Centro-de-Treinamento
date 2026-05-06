<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class FormularioDTO extends BaseDTO {
    public int $id;
    public string $nome;
    public ?string $descricao = null;

    public int $versao = 1;
    public bool $ativo = true;

    /** @var \Anamnese\DTO\PerguntaDTO[] */
    public array $perguntas = [];
}