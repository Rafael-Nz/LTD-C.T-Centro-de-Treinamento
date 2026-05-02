<?php
namespace Aluno\DTO;

use Usuario\DTO\UsuarioDTO;

/**
 * DTO específico para Aluno
 * Estende UsuarioDTO para reaproveitar todos os campos de usuário
 */
class AlunoDTO extends UsuarioDTO
{
    // Campos específicos do aluno
    public ?string $data_matricula = null;      // Se não informado, assume data atual
    public ?string $codigo_matricula = null;    // Gerado automaticamente (não vem da request)
    public ?int $cadastrado_por = null;         // ID do funcionário que cadastrou
}