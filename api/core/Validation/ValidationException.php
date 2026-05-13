<?php
namespace Core\Validation;

class ValidationException extends \InvalidArgumentException {
    private array $errors;

    public function __construct(
        array $errors,
        string $message = ''
    ) {
        $this->errors = $errors;
        parent::__construct($message !== '' ? $message : $this->buildMessage($errors));
    }

    public function errors(): array {
        return $this->errors;
    }

    private function buildMessage(array $errors): string {
        $messages = [];
        foreach ($errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'Os dados informados sao invalidos.';
    }
}
