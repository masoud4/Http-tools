<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class IpRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be a valid IP address.";
    }
}