<?php

namespace Core\Auth;

class PasswordPolicy
{
    public const MIN_LENGTH = 12;
    public const MAX_LENGTH = 128;

    public static function generate(int $length = 16): string
    {
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('Tamanho de senha inicial invalido.');
        }

        $groups = [
            '0123456789',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            '!@#$%^&*()-_=+[]{}:,.?/',
        ];
        $allCharacters = implode('', $groups);
        $characters = [];

        foreach ($groups as $group) {
            $characters[] = $group[random_int(0, strlen($group) - 1)];
        }

        while (count($characters) < $length) {
            $characters[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        for ($index = count($characters) - 1; $index > 0; $index--) {
            $swapIndex = random_int(0, $index);
            [$characters[$index], $characters[$swapIndex]] = [$characters[$swapIndex], $characters[$index]];
        }

        return implode('', $characters);
    }

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
