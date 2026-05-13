<?php
namespace Core\Validation;

interface ValidationRuleInterface {
    public function validate(string $field, mixed $value, array $data, callable $fail): void;
}
