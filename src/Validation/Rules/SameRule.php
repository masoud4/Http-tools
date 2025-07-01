<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class SameRule implements ValidationRuleInterface
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
            // This rule implicitly fails if the other field doesn't exist to prevent comparison issues.
            return false;
        }
        return $value === $data[$this->otherField];
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must match {$this->otherField}.";
    }
}