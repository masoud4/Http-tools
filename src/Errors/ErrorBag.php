<?php
namespace Classic\HttpTools\Errors; // Correct namespace

class ErrorBag
{
    private array $errors = [];

    /**
     * Add an error message for a specific field.
     * @param string $field The field name the error relates to.
     * @param string $message The error message.
     * @return void
     */
    public function add(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Check if there are any errors.
     * @param string|null $field Optional field name to check for errors.
     * @return bool
     */
    public function has(string $field = null): bool
    {
        if ($field === null) {
            return !empty($this->errors);
        }
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Get all errors, or errors for a specific field.
     * @param string|null $field Optional field name to get errors for.
     * @return array
     */
    public function get(string $field = null): array
    {
        if ($field === null) {
            return $this->errors;
        }
        return $this->errors[$field] ?? [];
    }

    /**
     * Get the first error message for a given field.
     * @param string $field The field name.
     * @return string|null The first error message, or null if no errors for the field.
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get all error messages as a flat array.
     * @return array
     */
    public function all(): array
    {
        $allErrors = [];
        foreach ($this->errors as $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return $allErrors;
    }

    /**
     * Convert the error bag to a JSON string.
     * @return string
     */
    public function toJson(): string
    {
        // Fix: Explicitly cast to an object if empty to ensure JSON {} instead of []
        if (empty($this->errors)) {
            return json_encode((object)[]);
        }
        return json_encode($this->errors);
    }
}