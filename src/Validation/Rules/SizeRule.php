<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class SizeRule implements ValidationRuleInterface
{
    private float $size; // Always store the rule parameter as a float for consistent comparisons

    public function __construct(string $param)
    {
        // Echo for debugging: See what param is passed and how it's cast
        // echo "SizeRule Constructor: param='{$param}', cast to float: " . (float)$param . PHP_EOL;
        $this->size = (float) $param;
    }

   public function validate(string $field, mixed $value, array $data): bool
{
    if (is_scalar($value) && is_numeric($value)) {
        return abs((float) $value - $this->size) < 0.00001;
    }

    if (is_string($value)) {
        return mb_strlen($value) === (int) $this->size;
    }

    if (is_array($value)) {
        return count($value) === (int) $this->size;
    }

    return false;
}

    public function getMessage(string $field): string
    {
        return "The {$field} field must be {$this->size} characters, items, or value.";
    }
}