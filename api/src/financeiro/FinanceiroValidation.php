<?php
namespace Financeiro;

final class FinanceiroValidation {
    public static function centavos(mixed $value, string $field = 'Valor'): int {
        if ((!is_string($value) && !is_int($value) && !is_float($value))
            || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string) $value)) {
            throw new \InvalidArgumentException("$field deve ser positivo ou zero, com no maximo duas casas decimais.");
        }
        $parts = explode('.', (string) $value);
        return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    public static function decimal(int $centavos): string {
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function data(string $value, string $field = 'Data', bool $time = false): \DateTimeImmutable {
        $format = $time ? 'Y-m-d H:i:s' : 'Y-m-d';
        $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
        if (!$date || $date->format($format) !== $value || (int) $date->format('Y') < 1900) {
            throw new \InvalidArgumentException("$field invalida.");
        }
        return $date;
    }

    public static function motivo(string $value): string {
        $value = trim($value);
        if (mb_strlen($value) < 5 || mb_strlen($value) > 500) {
            throw new \InvalidArgumentException('Informe uma justificativa entre 5 e 500 caracteres.');
        }
        return $value;
    }
}
