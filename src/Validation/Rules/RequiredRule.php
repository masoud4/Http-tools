<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class RequiredRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return !empty($value);
        }
        return $value !== null && $value !== '';
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field is required.";
    }
}