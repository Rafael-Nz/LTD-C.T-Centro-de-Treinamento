<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class PerguntaDTO extends BaseDTO
{
    public int $id;
    public string $slug;
    public string $pergunta;

    public ?string $categoria = null;

    public string $tipo_input;

    public bool $obrigatoria = false;
    public int $ordem = 0;
    public int $versao = 1;
    public bool $ativo = true;

    public ?array $config = null;

    public ?RegraExibicaoDTO $regra_exibicao = null;

    /** @var \Anamnese\DTO\OpcaoDTO[] */
    public array $opcoes = [];
}