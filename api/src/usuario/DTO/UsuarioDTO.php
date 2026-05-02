<?php
namespace Usuario\DTO;

use Core\DTO\BaseDTO;

class UsuarioDTO extends BaseDTO
{
    // Dados pessoais obrigatórios
    public string $nome;
    public string $sobrenome;
    public string $cpf;
    public string $email;
    public string $data_nascimento;

    // Dados opcionais com valores padrão
    public string $genero = 'O';
    public string $tipo_usuario;
    public ?string $senha = null;
    public bool $ativo = true;

    // Relacionamentos (outros DTOs)
    public ?EnderecoDTO $endereco = null;

    /** @var \Usuario\DTO\ContatoDTO[] */
    public array $contatos = [];
}