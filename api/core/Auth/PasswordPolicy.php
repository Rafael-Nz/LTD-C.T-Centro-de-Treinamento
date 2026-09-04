<?php

namespace Core\Auth;

class PasswordPolicy
{
    public const MIN_LENGTH = 12;
    public const MAX_LENGTH = 128;

    public static function validate(string $password): void
    {
        $length = mb_strlen($password, 'UTF-8');
        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException('A senha deve ter pelo menos 12 caracteres.');
        }

        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('A senha deve ter no maximo 128 caracteres.');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('A senha deve conter pelo menos um numero.');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new \InvalidArgumentException('A senha deve conter pelo menos uma letra maiuscula.');
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new \InvalidArgumentException('A senha deve conter pelo menos uma letra minuscula.');
        }

        if (!preg_match('/[^A-Za-z0-9\s]/u', $password)) {
            throw new \InvalidArgumentException('A senha deve conter pelo menos um caractere especial.');
        }
    }
}
