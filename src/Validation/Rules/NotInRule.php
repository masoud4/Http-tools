<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class NotInRule implements ValidationRuleInterface
{
    private array $disallowedValues;

    public function __construct(string $param)
    {
        $rawValues = explode(',', $param);
        $this->disallowedValues = array_map(function($val) {
            return is_numeric($val) ? (str_contains($val, '.') ? (float)$val : (int)$val) : $val;
        }, $rawValues);
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (is_numeric($value)) {
            foreach ($this->disallowedValues as $disallowedVal) {
                if ($value == $disallowedVal) { // Loose comparison here
                    return false; // Found in disallowed, so invalid
                }
            }
            return true; // Not found in disallowed
        }
        return !in_array($value, $this->disallowedValues, true); // Strict comparison for non-numeric
    }

    public function getMessage(string $field): string
    {
        return "The selected {$field} is invalid.";
    }
}