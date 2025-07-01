<?php

namespace masoud4\HttpTools\Validation;

use masoud4\HttpTools\Errors\ErrorBag;
use masoud4\HttpTools\Validation\Rules\AlphaNumRule;
use masoud4\HttpTools\Validation\Rules\AlphaRule;
use masoud4\HttpTools\Validation\Rules\ArrayTypeRule;
use masoud4\HttpTools\Validation\Rules\BetweenRule;
use masoud4\HttpTools\Validation\Rules\BoolTypeRule;
use masoud4\HttpTools\Validation\Rules\DateRule;
use masoud4\HttpTools\Validation\Rules\DifferentRule;
use masoud4\HttpTools\Validation\Rules\EmailRule;
use masoud4\HttpTools\Validation\Rules\InRule;
use masoud4\HttpTools\Validation\Rules\IntTypeRule;
use masoud4\HttpTools\Validation\Rules\IpRule;
use masoud4\HttpTools\Validation\Rules\JsonRule;
use masoud4\HttpTools\Validation\Rules\MaxRule;
use masoud4\HttpTools\Validation\Rules\MinRule;
use masoud4\HttpTools\Validation\Rules\NotInRule;
use masoud4\HttpTools\Validation\Rules\RegexRule;
use masoud4\HttpTools\Validation\Rules\RequiredIfRule;
use masoud4\HttpTools\Validation\Rules\RequiredRule;
use masoud4\HttpTools\Validation\Rules\SameRule;
use masoud4\HttpTools\Validation\Rules\SizeRule;
use masoud4\HttpTools\Validation\Rules\StringTypeRule;
use masoud4\HttpTools\Validation\Rules\UrlRule;


class Validator
{
    private array $data;
    private array $rules;
    private ErrorBag $errorBag;
    private array $customMessages = [];
    private array $validatedData = [];

    // Static mapping of rule names to their class names or callables
    private static array $globalRuleMap = [
        'required' => RequiredRule::class,
        'email' => EmailRule::class,
        'string' => StringTypeRule::class,
        'int' => IntTypeRule::class,
        'min' => MinRule::class,
        'max' => MaxRule::class,
        'array' => ArrayTypeRule::class,
        'boolean' => BoolTypeRule::class,
        'size' => SizeRule::class,
        'between' => BetweenRule::class,
        'in' => InRule::class,
        'not_in' => NotInRule::class,
        'url' => UrlRule::class,
        'ip' => IpRule::class,
        'json' => JsonRule::class,
        'date' => DateRule::class,
        'regex' => RegexRule::class,
        'alpha' => AlphaRule::class,
        'alpha_num' => AlphaNumRule::class,
        'same' => SameRule::class,
        'different' => DifferentRule::class,
        'required_if' => RequiredIfRule::class,
    ];

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->data = $this->trimStrings($data);
        }
        $this->errorBag = new ErrorBag();
    }
    public function setData(array $data)
    {
        $this->data = $this->trimStrings($data);
    }

    /**
     * Statically register a custom validation rule class or callable.
     * @param string $ruleName The name of the rule.
     * @param string|\Closure $ruleDefinition Rule class name or a callable.
     * @return void
     */
    public static function extend(string $ruleName, string|\Closure $ruleDefinition): void
    {
        self::$globalRuleMap[$ruleName] = $ruleDefinition;
    }

    /**
     * Define the validation rules.
     * @param array $rules An associative array.
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set custom error messages for rules.
     * @param array $messages
     * @return self
     */
    public function setMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Quickly validate data with rules and custom messages in one call.
     * Returns validated data array on success, or false on failure.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return array|false
     */
    public function validateData(array $data, array $rules, array $messages = [])
    {
        $this->setData($data);
        $this->setRules($rules);
        $this->setMessages($messages);

        if ($this->validate()) {
            return $this->validatedData();
        }

        return false;
    }
    /**
     * Perform the validation.
     * @return bool True if all validation passes, false otherwise.
     */
    public function validate(): bool
    {
        $this->validatedData = []; // Reset validated data for each run

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;

            // Handle direct Closure rule for a field
            if ($fieldRules instanceof \Closure) {
                $isValid = true;
                $errorMessage = '';
                $result = call_user_func($fieldRules, $field, $value, $this->data, null);
                if ($result === false || is_string($result)) {
                    $isValid = false;
                    $errorMessage = is_string($result) ? $result : "The {$field} field is invalid.";
                }

                if (!$isValid) {
                    $this->errorBag->add($field, $errorMessage);
                } else {
                    $this->validatedData[$field] = $value;
                }
                continue; // Move to the next field
            }

            $parsedRules = $this->parseFieldRules($fieldRules);
            $bail = false;
            $hasRequiredRule = false;
            $hasRequiredIfRule = false;

            // First pass: identify 'bail' and 'required'/'required_if' rules
            foreach (array_keys($parsedRules) as $ruleName) {
                if ($ruleName === 'bail') {
                    $bail = true;
                } elseif ($ruleName === 'required') {
                    $hasRequiredRule = true;
                } elseif ($ruleName === 'required_if') {
                    $hasRequiredIfRule = true;
                }
            }

            // Determine if the field is actually required by any means
            $isActuallyRequired = false;
            if ($hasRequiredRule) {
                $isActuallyRequired = (new RequiredRule())->validate($field, $value, $this->data);
                if (!$isActuallyRequired) { // If required but empty, add error
                    $this->errorBag->add($field, $this->customMessages["{$field}.required"] ?? $this->customMessages['required'] ?? (new RequiredRule())->getMessage($field));
                    if ($bail) continue; // Skip further validation for this field if bail is active
                }
            } elseif ($hasRequiredIfRule) {
                // For required_if, we need to instantiate it to check its condition
                $ruleParam = $parsedRules['required_if']['param'];
                $requiredIfInstance = new RequiredIfRule($ruleParam);

                // If required_if's validate returns false, it means the field *is* required but empty
                if (!$requiredIfInstance->validate($field, $value, $this->data)) {
                    $isActuallyRequired = true; // It's required and empty
                    $this->errorBag->add($field, $this->customMessages["{$field}.required_if"] ?? $this->customMessages['required_if'] ?? $requiredIfInstance->getMessage($field));
                    if ($bail) continue; // Skip further validation for this field if bail is active
                }
                // If required_if's validate returns true, it means either:
                // 1. The condition for 'required_if' was NOT met (so field is NOT required)
                // 2. The condition WAS met AND the field IS NOT empty (so field passed its 'required' check)
            }


            // --- IMPLICIT OPTIONAL FIELD LOGIC REFINED ---
            // If the field is NOT actually required AND its value is effectively empty,
            // then skip further validation rules for this field.
            // This is crucial: if it's not required by 'required' or 'required_if' AND it's empty, we stop.
            if (!$isActuallyRequired && $this->isEmptyValue($value)) {
                $this->validatedData[$field] = $value; // Add empty value to validated data
                continue; // Skip all other rules for this field
            }
            // --- END IMPLICIT OPTIONAL FIELD LOGIC REFINED ---


            // Second pass: apply other rules
            foreach ($parsedRules as $ruleName => $ruleInfo) {
                // Skip 'bail' and 'required'/'required_if' as they were handled
                if (in_array($ruleName, ['bail', 'required', 'required_if'])) {
                    continue;
                }



                $ruleParam = $ruleInfo['param'];
                $customMessage = $ruleInfo['message'];

                $ruleDefinition = self::$globalRuleMap[$ruleName] ?? null;

                if ($ruleDefinition === null) {
                    $this->errorBag->add($field, "Unknown validation rule: {$ruleName}");
                    if ($bail) break;
                    continue;
                }

                $isValid = true;
                $errorMessage = '';

                if (is_string($ruleDefinition) && class_exists($ruleDefinition) && is_subclass_of($ruleDefinition, ValidationRuleInterface::class)) {
                    /** @var ValidationRuleInterface $ruleInstance */
                    $ruleInstance = new $ruleDefinition($ruleParam);
                    $isValid = $ruleInstance->validate($field, $value, $this->data);
                    if (!$isValid) {
                        $errorMessage = $ruleInstance->getMessage($field);
                    }
                } elseif ($ruleDefinition instanceof \Closure) {
                    $result = call_user_func($ruleDefinition, $field, $value, $this->data, $ruleParam);
                    if ($result === false || is_string($result)) {
                        $isValid = false;
                        $errorMessage = is_string($result) ? $result : "The {$field} field is invalid.";
                    }
                } else {
                    $this->errorBag->add($field, "Invalid rule definition for {$ruleName}.");
                    if ($bail) break;
                    continue;
                }

                if (!$isValid) {
                    $messageToUse = $this->customMessages["{$field}.{$ruleName}"] ?? $this->customMessages[$ruleName] ?? $errorMessage;
                    $this->errorBag->add($field, $messageToUse);
                    if ($bail) break;
                }
            }
            // Add to validated data ONLY if no errors were found for this field during this validation cycle
            if (!$this->errorBag->has($field)) {
                $this->validatedData[$field] = $value;
            }
        }
        $this->validatedData = $this->castValidatedData($this->validatedData, $this->rules);
        return !$this->errorBag->has();
    }
    /**
     * Validate data with rules and messages, or exit with error message.
     * This is a controller-friendly shortcut for validation with immediate failure handling.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param callable|null $onError Optional closure to handle errors (e.g. redirect)
     * @return array
     */
    public function validateOrFail(array $data, array $rules, array $messages = [], ?callable $onError = null): array
    {
        $this->setData($data);
        $this->setRules($rules);
        $this->setMessages($messages);

        if ($this->validate()) {
            return $this->validatedData();
        }

        $errors = $this->errors()->get();

        if ($onError) {
            // Let user handle the failure (redirect, flash, etc.)
            $onError($errors);
            exit;
        }

        // Default behavior: exit with JSON error
        header('Content-Type: application/json', true, 400);
        exit(json_encode([
            'success' => false,
            'errors' => $errors,
        ]));
    }

    /**
     * Check if a value is effectively empty.
     * @param mixed $value
     * @return bool
     */
    private function isEmptyValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }
        // For files, consider it empty if UPLOAD_ERR_NO_FILE
        // Note: For 'required' rules on files, it's typically checked if 'tmp_name' is empty/file not uploaded
        if (is_array($value) && isset($value['error']) && $value['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        return false;
    }

    /**
     * Get the error bag.
     * @return ErrorBag
     */
    public function errors(): ErrorBag
    {
        return $this->errorBag;
    }

    /**
     * Get the data that passed validation, with casting applied.
     * @return array
     */
    public function validatedData(): array
    {
        return $this->validatedData;
    }

    /**
     * Parse the rules for a single field, handling string or array formats.
     * @param string|array $fieldRules
     * @return array Rules parsed into ruleName => ['param' => string|null, 'message' => string|null]
     */
    private function parseFieldRules(string|array $fieldRules): array
    {
        $parsed = [];
        $rulesArray = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

        foreach ($rulesArray as $key => $value) {
            if (is_int($key)) {
                list($ruleName, $ruleParam) = array_pad(explode(':', $value, 2), 2, null);
                $parsed[$ruleName] = ['param' => $ruleParam, 'message' => null];
            } else {
                list($ruleName, $ruleParam) = array_pad(explode(':', $key, 2), 2, null);
                $parsed[$ruleName] = ['param' => $ruleParam, 'message' => $value];
            }
        }
        return $parsed;
    }

    /**
     * Recursively trim strings in the input data.
     * @param array $data
     * @return array
     */
    private function trimStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->trimStrings($value);
            }
        }
        return $data;
    }

    /**
     * Cast validated data based on inferred rule types.
     * @param array $data
     * @param array $rules
     * @return array
     */
    private function castValidatedData(array $data, array $rules): array
    {
        foreach ($data as $field => $value) {
            if (!isset($rules[$field])) {
                continue;
            }

            // Skip casting if the rule was a direct Closure
            if ($rules[$field] instanceof \Closure) {
                continue;
            }

            $fieldRules = $this->parseFieldRules($rules[$field]);
            foreach (array_keys($fieldRules) as $ruleName) {
                switch ($ruleName) {
                    case 'int':
                        $data[$field] = (int) $value;
                        break;
                    case 'boolean':
                        $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$value;
                        break;
                    case 'float': // If you add a float rule
                        $data[$field] = (float) $value;
                        break;
                        // String and array types usually don't need explicit casting unless they are not already
                }
            }
        }
        return $data;
    }
}
