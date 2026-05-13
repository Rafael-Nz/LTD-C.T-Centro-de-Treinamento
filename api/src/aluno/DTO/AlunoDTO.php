<?php
namespace Aluno\DTO;

use Usuario\DTO\UsuarioDTO;

class AlunoDTO extends UsuarioDTO
{
    public ?string $data_matricula = null;
    public ?string $codigo_matricula = null;
    public ?int $cadastrado_por = null;
    public ?array $turma_ids = null;
}
