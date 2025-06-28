<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class JsonRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_string($value)) {
            return false;
        }
        json_decode($value);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be a valid JSON string.";
    }
}