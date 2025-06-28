<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class AlphaNumRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }
        return ctype_alnum((string)$value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must only contain letters and numbers.";
    }
}