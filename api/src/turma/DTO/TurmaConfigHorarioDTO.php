<?php
namespace Turma\DTO;

use Core\DTO\BaseDTO;

class TurmaConfigHorarioDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $dia_semana = null;
    public ?string $hora_inicio = null;
    public ?string $hora_fim = null;
}
