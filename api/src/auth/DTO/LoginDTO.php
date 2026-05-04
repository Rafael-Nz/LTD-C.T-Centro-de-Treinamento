<?php
namespace Auth\DTO;

use Core\DTO\BaseDTO;

class LoginDTO extends BaseDTO
{
    public string $login;
    public string $senha;
}