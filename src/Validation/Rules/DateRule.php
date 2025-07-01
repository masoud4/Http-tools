<?php
namespace masoud4\HttpTools\Validation\Rules;

use masoud4\HttpTools\Validation\ValidationRuleInterface;

class DateRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        // 1. Must be a string and not empty/whitespace
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        // 2. Define strict formats to check against
        // This makes the rule behave predictably for validation purposes.
        $strictFormats = [
            'Y-m-d',
            'Y/m/d',
            'm-d-Y', // e.g., 10-25-2023
            'm/d/Y', // e.g., 10/25/2023
            'd-m-Y', // e.g., 25-10-2023
            'd/m/Y', // e.g., 25/10/2023
            'Y-m-d H:i:s', // Date with full time
            'Y/m/d H:i:s',
            'Y-m-d H:i', // Date with hour and minute
            'Y/m/d H:i',
        ];

        foreach ($strictFormats as $format) {
            $d = \DateTime::createFromFormat($format, $value);
            // If DateTime object is created AND the formatted string matches the original value, it's valid.
            if ($d && $d->format($format) === $value) {
                return true;
            }
        }

        // If none of the strict formats matched, it's not a valid date for this rule.
        return false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be a valid date.";
    }
}