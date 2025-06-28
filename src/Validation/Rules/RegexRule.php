<?php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class RegexRule implements ValidationRuleInterface
{
    private string $pattern;

    public function __construct(string $param)
    {
        // Ensure pattern is properly formatted for preg_match
        $this->pattern = $param;
        if (substr($this->pattern, 0, 1) !== '/') {
            $this->pattern = '/' . $this->pattern . '/';
        }
        if (substr($this->pattern, -1, 1) === '/') {
            // Already has delimiter, good
        } else {
            // Assume it needs closing delimiter if none provided, simple case
            $this->pattern .= '/';
        }
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return preg_match($this->pattern, $value) === 1;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field format is invalid.";
    }
}