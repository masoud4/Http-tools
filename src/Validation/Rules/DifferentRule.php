<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class DifferentRule implements ValidationRuleInterface
{
    private string $otherField;

    public function __construct(string $param)
    {
        $this->otherField = $param;
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        // Check if the other field exists in the data
        if (!array_key_exists($this->otherField, $data)) {
            // This rule implicitly passes if the other field doesn't exist,
            // or you could add an error for misconfigured rule.
            return true;
        }
        return $value !== $data[$this->otherField];
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be different from {$this->otherField}.";
    }
}