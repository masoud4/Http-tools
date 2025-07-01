<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class MaxRule implements ValidationRuleInterface
{
    private float $max;

    public function __construct(string $param)
    {
        $this->max = (float) $param;
    }

    public function validate(string $field, mixed $value, array $data): bool
    {

        if (is_numeric($value)) {
            return $value <= $this->max;
        }
        if (is_string($value)) {
            return mb_strlen($value) <= $this->max;
        }
       
        if (is_array($value)) {
            return count($value) <= $this->max;
        }
        return false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} may not be greater than {$this->max} characters or value.";
    }
}