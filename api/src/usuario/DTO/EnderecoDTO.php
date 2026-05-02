<?php
namespace Usuario\DTO;

use Core\DTO\BaseDTO;

class EnderecoDTO extends BaseDTO
{
    public string $logradouro;
    public string $numero;
    public string $cidade;
    public string $bairro;
    public string $cep;
    public ?string $complemento = null;
}