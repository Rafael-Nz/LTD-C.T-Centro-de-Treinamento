<?php
namespace Anamnese\DTO;

use Core\DTO\BaseDTO;

class RegraExibicaoDTO extends BaseDTO {
    public ?CondicaoDTO $if = null;

    /** @var \Anamnese\DTO\CondicaoDTO[] */
    public array $conditions = [];

    public string $logic = 'AND'; // AND | OR

}