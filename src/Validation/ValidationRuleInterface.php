<?php
namespace masoud4\HttpTools\Validation; 

interface ValidationRuleInterface
{
    /**
     * Validate a given value against this rule.
     * @param string $field The name of the field being validated.
     * @param mixed $value The value to validate.
     * @param array $data The entire data array being validated.
     * @return bool True if validation passes, false otherwise.
     */
    public function validate(string $field, mixed $value, array $data): bool;

    /**
     * Get the error message if validation fails.
     * @param string $field The name of the field that failed validation.
     * @return string The error message.
     */
    public function getMessage(string $field): string;
}