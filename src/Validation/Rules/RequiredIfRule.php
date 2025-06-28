<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class RequiredIfRule implements ValidationRuleInterface
{
    private string $otherField;
    private string $expectedValue;

    public function __construct(string $param)
    {
        list($this->otherField, $this->expectedValue) = explode(',', $param, 2);
    }

    /**
     * @param string $field The name of the field being validated.
     * @param mixed $value The value to validate.
     * @param array $data The entire data array being validated.
     * @return bool True if validation passes, false otherwise.
     * Returns true if the condition is not met (field is not required).
     * Otherwise, it behaves like a RequiredRule.
     */
    public function validate(string $field, mixed $value, array $data): bool
    {
        // Check if the condition for requiring the field is met
        if (isset($data[$this->otherField]) && (string)$data[$this->otherField] === $this->expectedValue) {
            // Condition met: the field *is* required. Now check if it's empty.
            return (new RequiredRule())->validate($field, $value, $data);
        }

        // Condition not met: the field is NOT required. Always return true.
        // The Validator will then skip other rules for this field if it's empty.
        return true;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field is required when {$this->otherField} is {$this->expectedValue}.";
    }
}