<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class IntTypeRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        // filter_var is good for checking if a string can be an int
        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }
        return is_int($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be an integer.";
    }
}