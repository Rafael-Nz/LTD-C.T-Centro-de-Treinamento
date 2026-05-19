<?php
namespace Treino\DTO;

use Core\DTO\BaseDTO;

class TreinoDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $nome = null;
    public ?int $modalidade_id = null;
    public ?string $descricao = null;
    public bool $ativo = true;
    public ?string $data_criacao = null;
    public ?string $data_atualizacao = null;
    public ?string $modalidade_nome = null;
}
