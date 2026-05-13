<?php
namespace Core\Validation;

interface ValidatorInterface {
    public function validate(array|object $data, array $rules, array $messages = [], array $attributes = []): array;
}
