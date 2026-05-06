<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class RespostaDTO extends BaseDTO
{
    /**
     * Nullable porque aluno_id só existe no EnvioAnamneseDTO (envelope),
     * não em cada resposta individual enviada pelo JS.
     * AnamneseService::salvar() usa $dto->aluno_id, nunca $respostaDTO->aluno_id.
     */
    public ?int $aluno_id = null;

    public int $pergunta_id;

    public mixed $valor; // pode ser bool, string, array, number

    public ?string $observacao = null;
}
