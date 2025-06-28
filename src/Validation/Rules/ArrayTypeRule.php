<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class ArrayTypeRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return is_array($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be an array.";
    }
}