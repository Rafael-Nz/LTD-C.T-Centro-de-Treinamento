<?php
namespace Turma\DTO;

use Core\DTO\BaseDTO;

/**
 * DTO para Turma
 * Encapsula os dados de uma turma com type safety
 */
class TurmaDTO extends BaseDTO
{
    // ID (apenas para retorno/update)
    public ?int $id = null;

    // Campos obrigatórios
    public string $nome;
    public string $turno;        // 'manha', 'tarde', 'noite'
    public int $capacidade_minima;
    public int $capacidade_maxima;
    public int $instrutor_id;
    public int $modalidade_id;   // FK real da tabela turma

    // Campos opcionais
    public bool $ativo = true;

    // Timestamps (apenas para retorno)
    public ?string $data_criacao = null;
    public ?string $data_atualizacao = null;

    // Dados relacionados - Instrutor (apenas para retorno)
    public ?string $instrutor_nome = null;
    public ?string $instrutor_email = null;
    public ?string $instrutor_registro_profissional = null;

    // Dados relacionados - Modalidade (apenas para retorno)
    public ?string $modalidade_nome = null;
    public ?string $modalidade_descricao = null;
}
