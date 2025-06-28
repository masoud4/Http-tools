<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class MaxRule implements ValidationRuleInterface
{
    private float $max;

    public function __construct(string $param)
    {
        $this->max = (float) $param;
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) <= $this->max;
        }
        if (is_numeric($value)) {
            return (float) $value <= $this->max;
        }
        if (is_array($value)) {
            return count($value) <= $this->max;
        }
        return false; // Cannot validate max for other types
    }

    public function getMessage(string $field): string
    {
        return "The {$field} may not be greater than {$this->max} characters or value.";
    }
}