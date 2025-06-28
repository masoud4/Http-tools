<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class BoolTypeRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (is_bool($value)) {
            return true;
        }
        if (is_string($value)) {
            $normalized = strtolower($value);
            return in_array($normalized, ['true', 'false', '1', '0', 'yes', 'no'], true);
        }
        if (is_numeric($value)) {
            return in_array($value, [0, 1], true);
        }
        return false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be a boolean (true/false, 1/0).";
    }
}