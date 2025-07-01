<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class BetweenRule implements ValidationRuleInterface
{
    private float $min;
    private float $max;

    public function __construct(string $param)
    {
        list($min, $max) = explode(',', $param);
        $this->min = (float) $min;
        $this->max = (float) $max;
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        $val = (float) $value;
        return $val >= $this->min && $val <= $this->max;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be between {$this->min} and {$this->max}.";
    }
}