<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class EnvioAnamneseDTO extends BaseDTO {
    public int $aluno_id;

    public int $formulario_id = 1;
    
    /** @var \Anamnese\DTO\RespostaDTO[] */
    public array $respostas = [];
}
