<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\MaxRule;

class MaxRuleTest extends TestCase
{
    public function testValidValues(): void
    {
        $rule = new MaxRule('5'); // Max length 5 or max value 5
        $this->assertTrue($rule->validate('field', 'abc', []));
        $this->assertTrue($rule->validate('field', 'abcde', []));
        $this->assertTrue($rule->validate('field', 3, []));
        $this->assertTrue($rule->validate('field', 5, []));
        $this->assertTrue($rule->validate('field', '3.5', [])); // Numeric string
        $this->assertTrue($rule->validate('field', [1, 2], [])); // Array count
        $this->assertTrue($rule->validate('field', [], [])); // Empty array

        $rule = new MaxRule('5.5');
        $this->assertTrue($rule->validate('field', 5.5, []));
    }

    public function testInvalidValues(): void
    {
        $rule = new MaxRule('5');
        $this->assertFalse($rule->validate('field', 'abcdef', [])); // String too long
        $this->assertFalse($rule->validate('field', 6, []));        // Number too large
        $this->assertFalse($rule->validate('field', [1, 2, 3, 4, 5, 6], [])); // Array too large
    }

    public function testGetMessage(): void
    {
        $rule = new MaxRule('10');
        $this->assertEquals('The description may not be greater than 10 characters or value.', $rule->getMessage('description'));
    }
}