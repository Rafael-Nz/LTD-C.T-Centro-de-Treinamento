<?php
namespace Funcionario\DTO;

use Usuario\DTO\UsuarioDTO;

/**
 * DTO específico para Funcionário
 * Estende UsuarioDTO para reaproveitar todos os campos de usuário
 */
class FuncionarioDTO extends UsuarioDTO
{
    // Campos específicos do funcionário
    public ?int $cargo_id = null;                   // ID do cargo (obrigatório)
    public ?string $registro_profissional = null;   // Opcional
    public ?string $observacoes = null;             // Opcional
}