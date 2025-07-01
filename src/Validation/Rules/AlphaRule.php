<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class AlphaRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return ctype_alpha($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must only contain letters.";
    }
}