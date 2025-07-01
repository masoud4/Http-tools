<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class InRule implements ValidationRuleInterface
{
    private array $allowedValues;

    public function __construct(string $param)
    {
        $rawValues = explode(',', $param);
        $this->allowedValues = array_map(function($val) {
            // Attempt to cast to int/float if it's purely numeric
            return is_numeric($val) ? (str_contains($val, '.') ? (float)$val : (int)$val) : $val;
        }, $rawValues);
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        // Allow loose comparison if value is numeric, otherwise strict.
        // This is a common flexibility for `in` rules.
        if (is_numeric($value)) {
            // Check if numeric value matches any of the stored values (which might be strings or numbers)
            foreach ($this->allowedValues as $allowedVal) {
                if ($value == $allowedVal) { // Loose comparison here
                    return true;
                }
            }
            return false;
        }
        return in_array($value, $this->allowedValues, true); // Strict comparison for non-numeric
    }

    public function getMessage(string $field): string
    {
        return "The selected {$field} is invalid.";
    }
}