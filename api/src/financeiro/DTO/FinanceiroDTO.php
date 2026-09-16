<?php
namespace Financeiro\DTO;

use Core\DTO\BaseDTO;
use Financeiro\FinanceiroValidation as V;

abstract class FinanceiroDTO extends BaseDTO {
    public function __construct(array $data) {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) continue;
            $type = (new \ReflectionProperty($this, $key))->getType();
            if ($value === null && $type->allowsNull()) continue;
            if ($type->getName() === 'float') V::centavos($value, $key);
            if ($type->getName() === 'int' && (!is_scalar($value) || is_bool($value) || !preg_match('/^\d{1,9}$/D', (string) $value))) {
                throw new \InvalidArgumentException("$key deve ser um inteiro valido.");
            }
            if ($type->getName() === 'array' && !is_array($value)) throw new \InvalidArgumentException("$key deve ser uma lista.");
            if ($type->getName() === 'bool' && !in_array($value, [true, false, 0, 1], true)) throw new \InvalidArgumentException("$key invalido.");
            if ($type->getName() === 'string' && !is_string($value)) throw new \InvalidArgumentException("$key deve ser texto.");
        }
        parent::__construct($data);
    }
}
