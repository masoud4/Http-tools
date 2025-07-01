<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class StringTypeRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return is_string($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be a string.";
    }
}