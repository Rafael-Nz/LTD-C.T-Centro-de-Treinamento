<?php
namespace Treino\DTO;

use Core\DTO\BaseDTO;

class TreinoAgendaDTO extends BaseDTO
{
    public ?int $id = null;
    public ?int $treino_id = null;
    public ?int $turma_id = null;
    public ?int $espaco_id = null;
    public ?int $instrutor_id = null;
    public ?string $data_hora_inicio = null;
    public ?string $data_hora_fim = null;
    public ?string $status = 'agendado';
    public ?string $observacoes = null;
}
