<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class MinRule implements ValidationRuleInterface
{
    private float $min; // Always store as float for min/max comparison

    public function __construct(string $param)
    {
        $this->min = (float) $param; // Ensure the parameter is cast to float
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        // IMPORTANT FIX: Check if value is numeric *first* (this includes numeric strings like '5' or '5.0')
        if (is_numeric($value)) {
            return (float) $value >= $this->min;
        }
        // If not numeric, then check if it's a string
        if (is_string($value)) {
            return mb_strlen($value) >= $this->min;
        }
        // If not numeric or string, then check if it's an array
        if (is_array($value)) {
            return count($value) >= $this->min;
        }
        return false; // For other types, validation fails
    }

    public function getMessage(string $field): string
    {
        // The message now uses a more general wording to cover strings, numbers, and arrays
        return "The {$field} must be at least {$this->min} characters, items, or value.";
    }
}
