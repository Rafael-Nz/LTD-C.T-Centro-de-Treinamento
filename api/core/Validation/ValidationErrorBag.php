<?php
namespace Core\Validation;

class ValidationErrorBag {
    private array $errors = [];

    public function add(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }

    public function all(): array {
        return $this->errors;
    }

    public function first(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }

    public function firstMessage(): string {
        foreach ($this->errors as $messages) {
            if (!empty($messages)) {
                return $messages[0];
            }
        }

        return 'Os dados informados sao invalidos.';
    }

    public function isEmpty(): bool {
        return empty($this->errors);
    }
}
