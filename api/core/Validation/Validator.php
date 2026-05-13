<?php
namespace Core\Validation;

use Core\DTO\BaseDTO;

class Validator implements ValidatorInterface {
    private ValidationErrorBag $errors;
    private array $messages = [];
    private array $attributes = [];

    public static function make(array|object $data, array $rules, array $messages = [], array $attributes = []): self {
        $validator = new self();
        $validator->validate($data, $rules, $messages, $attributes);
        return $validator;
    }

    public function validate(array|object $data, array $rules, array $messages = [], array $attributes = []): array {
        $this->errors = new ValidationErrorBag();
        $this->messages = $messages;
        $this->attributes = $attributes;

        $normalized = $this->normalizeData($data);

        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $normalized, is_array($fieldRules) ? $fieldRules : [$fieldRules]);
        }

        if (!$this->errors->isEmpty()) {
            throw new ValidationException($this->errors->all(), $this->errors->firstMessage());
        }

        return $normalized;
    }

    public function errors(): array {
        return isset($this->errors) ? $this->errors->all() : [];
    }

    public function fails(): bool {
        return !empty($this->errors());
    }

    public function firstErrorMessage(): string {
        return isset($this->errors) ? $this->errors->firstMessage() : 'Os dados informados sao invalidos.';
    }

    private function validateField(string $field, array $data, array $rules): void {
        $exists = array_key_exists($field, $data);
        $value = $data[$field] ?? null;
        $required = $this->containsNamedRule($rules, 'required');
        $nullable = $this->containsNamedRule($rules, 'nullable');

        if (!$exists && !$required) {
            return;
        }

        if (($value === null || $value === '') && !$required) {
            if ($nullable || $value === null || $value === '') {
                return;
            }
        }

        foreach ($rules as $rule) {
            if ($rule === 'nullable') {
                if ($value === null || $value === '') {
                    return;
                }
                continue;
            }

            if (is_string($rule)) {
                [$ruleName, $parameter] = $this->parseStringRule($rule);
                $this->applyStringRule($field, $value, $data, $ruleName, $parameter);
                continue;
            }

            if ($rule instanceof ValidationRuleInterface) {
                $rule->validate($field, $value, $data, function (string $message) use ($field) {
                    $this->errors->add($field, $message);
                });
                continue;
            }

            if (is_callable($rule)) {
                $rule($field, $value, $data, function (string $message) use ($field) {
                    $this->errors->add($field, $message);
                });
            }
        }
    }

    private function applyStringRule(string $field, mixed $value, array $data, string $ruleName, ?string $parameter): void {
        $label = $this->attributeLabel($field);

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                    $this->addError($field, $ruleName, "{$label} e obrigatorio.");
                }
                return;

            case 'string':
                if (!is_string($value)) {
                    $this->addError($field, $ruleName, "{$label} deve ser texto.");
                }
                return;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, $ruleName, "{$label} deve ser numerico.");
                }
                return;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, $ruleName, "{$label} deve ser um numero inteiro.");
                }
                return;

            case 'array':
                if (!is_array($value)) {
                    $this->addError($field, $ruleName, "{$label} deve ser uma lista.");
                }
                return;

            case 'min':
                if ($this->isSizeRuleValid($value) && $this->resolveComparableSize($value) < (float) $parameter) {
                    $this->addError($field, $ruleName, "{$label} deve ser maior ou igual a {$parameter}.");
                }
                return;

            case 'max':
                if ($this->isSizeRuleValid($value) && $this->resolveComparableSize($value) > (float) $parameter) {
                    $this->addError($field, $ruleName, "{$label} deve ser menor ou igual a {$parameter}.");
                }
                return;

            case 'max_length':
                if (is_string($value) && mb_strlen($value) > (int) $parameter) {
                    $this->addError($field, $ruleName, "{$label} nao pode exceder {$parameter} caracteres.");
                }
                return;

            case 'in':
                $values = array_map('trim', explode(',', (string) $parameter));
                if (!in_array((string) $value, $values, true)) {
                    $this->addError($field, $ruleName, "{$label} possui um valor invalido.");
                }
                return;

            case 'date':
                if (!$this->isValidDate($value)) {
                    $this->addError($field, $ruleName, "{$label} deve ser uma data valida.");
                }
                return;

            case 'before_field':
                $other = $data[$parameter] ?? null;
                if ($this->isValidDate($value) && $this->isValidDate($other) && strtotime((string) $value) >= strtotime((string) $other)) {
                    $this->addError($field, $ruleName, "{$label} deve ser anterior a " . $this->attributeLabel((string) $parameter) . '.');
                }
                return;

            case 'after_field':
                $other = $data[$parameter] ?? null;
                if ($this->isValidDate($value) && $this->isValidDate($other) && strtotime((string) $value) <= strtotime((string) $other)) {
                    $this->addError($field, $ruleName, "{$label} deve ser posterior a " . $this->attributeLabel((string) $parameter) . '.');
                }
                return;

            case 'less_than_field':
                $other = $data[$parameter] ?? null;
                if (is_numeric($value) && is_numeric($other) && (float) $value >= (float) $other) {
                    $this->addError($field, $ruleName, "{$label} deve ser menor que " . $this->attributeLabel((string) $parameter) . '.');
                }
                return;

            case 'greater_than_field':
                $other = $data[$parameter] ?? null;
                if (is_numeric($value) && is_numeric($other) && (float) $value <= (float) $other) {
                    $this->addError($field, $ruleName, "{$label} deve ser maior que " . $this->attributeLabel((string) $parameter) . '.');
                }
                return;
        }
    }

    private function normalizeData(array|object $data): array {
        if ($data instanceof BaseDTO) {
            return $this->normalizeData($data->toArray());
        }

        if (is_array($data)) {
            $normalized = [];
            foreach ($data as $key => $value) {
                $normalized[$key] = $this->normalizeValue($value);
            }
            return $normalized;
        }

        return $this->normalizeData(get_object_vars($data));
    }

    private function normalizeValue(mixed $value): mixed {
        if ($value instanceof BaseDTO) {
            return $this->normalizeData($value);
        }

        if (is_object($value)) {
            return $this->normalizeData(get_object_vars($value));
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item) => $this->normalizeValue($item), $value);
        }

        return $value;
    }

    private function containsNamedRule(array $rules, string $target): bool {
        foreach ($rules as $rule) {
            if (is_string($rule) && $this->parseStringRule($rule)[0] === $target) {
                return true;
            }
        }

        return false;
    }

    private function parseStringRule(string $rule): array {
        $parts = explode(':', $rule, 2);
        return [$parts[0], $parts[1] ?? null];
    }

    private function addError(string $field, string $rule, string $defaultMessage): void {
        $message = $this->messages["{$field}.{$rule}"]
            ?? $this->messages[$rule]
            ?? $defaultMessage;

        $this->errors->add($field, $message);
    }

    private function attributeLabel(string $field): string {
        return $this->attributes[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    private function isSizeRuleValid(mixed $value): bool {
        return is_numeric($value) || is_string($value) || is_array($value);
    }

    private function resolveComparableSize(mixed $value): float|int {
        if (is_numeric($value)) {
            return $value + 0;
        }

        if (is_string($value)) {
            return mb_strlen($value);
        }

        return count((array) $value);
    }

    private function isValidDate(mixed $value): bool {
        return is_string($value) && strtotime($value) !== false;
    }
}
