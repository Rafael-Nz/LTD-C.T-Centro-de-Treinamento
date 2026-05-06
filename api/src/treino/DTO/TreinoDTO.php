<?php
namespace Treino\DTO;

use Core\DTO\BaseDTO;

class TreinoDTO extends BaseDTO
{
    public ?int    $id               = null;
    public int     $turma_id;
    public int     $espaco_id; 
    public string  $data_hora_inicio;
    public string  $data_hora_fim;
    public string  $status           = 'agendado';
}