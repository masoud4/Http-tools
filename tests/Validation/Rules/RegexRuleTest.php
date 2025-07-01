<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\RegexRule;

class RegexRuleTest extends TestCase
{
    public function testValidRegexPatterns(): void
    {
        // Simple numeric pattern
        $rule = new RegexRule('^\d+$');
        $this->assertTrue($rule->validate('field', '12345', []));
        $this->assertFalse($rule->validate('field', 'abc', []));

        // Password pattern (example from form)
        $passwordRegex = '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$';
        $rule = new RegexRule($passwordRegex);
        $this->assertTrue($rule->validate('field', 'StrongP@ss1', []));
        $this->assertFalse($rule->validate('field', 'weakpass', [])); // No uppercase/number/special
    }

    public function testInvalidRegexPatterns(): void
    {
        $rule = new RegexRule('^[A-Z]{3}$'); // Exactly 3 uppercase letters
        $this->assertFalse($rule->validate('field', 'ABCD', []));
        $this->assertFalse($rule->validate('field', 'AB', []));
        $this->assertFalse($rule->validate('field', 'abc', []));
    }

    public function testNonStringValues(): void
    {
        $rule = new RegexRule('^\d+$');
        $this->assertFalse($rule->validate('field', 123, [])); // Regex needs string, but rule casts
        $this->assertFalse($rule->validate('field', null, []));
        $this->assertFalse($rule->validate('field', [], []));
    }

    public function testGetMessage(): void
    {
        $rule = new RegexRule('^pattern$');
        $this->assertEquals('The code field format is invalid.', $rule->getMessage('code'));
    }
}