<?php
namespace Turma\DTO;

use Core\DTO\BaseDTO;

/**
 * DTO para Turma
 * Encapsula os dados de uma turma com tipagem segura
 */
class TurmaDTO extends BaseDTO {
    // ID (apenas para retorno/update)
    public ?int $id = null;

    // Campos obrigatorios
    public ?string $nome = null;
    public ?int $capacidade_minima = null;
    public ?int $capacidade_maxima = null;
    public ?int $instrutor_id = null;

    // Campos opcionais
    public bool $ativo = true;

    /**
     * @var \Turma\DTO\TurmaConfigHorarioDTO[]|null
     */
    public ?array $config_horarios = null;

    // Timestamps (apenas para retorno)
    public ?string $data_criacao = null;
    public ?string $data_atualizacao = null;

    // Dados relacionados - Instrutor (apenas para retorno)
    public ?string $instrutor_nome = null;
    public ?string $instrutor_email = null;
    public ?string $instrutor_registro_profissional = null;

    // Dados relacionados - Alunos (apenas para retorno)
    public ?int $total_alunos = null;
    public ?array $alunos = null;
}
