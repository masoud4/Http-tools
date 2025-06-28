<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\MinRule;

class MinRuleTest extends TestCase
{
    public function testValidValues(): void
    {
        $rule = new MinRule('5'); // Min length 5 or min value 5
        $this->assertTrue($rule->validate('field', 'abcde', []));
        $this->assertTrue($rule->validate('field', 'abcdef', []));
        $this->assertTrue($rule->validate('field', 5, []));
        $this->assertTrue($rule->validate('field', 6, []));
        $this->assertTrue($rule->validate('field', '5.0', [])); // Numeric string as float
        $this->assertTrue($rule->validate('field', [1, 2, 3, 4, 5], [])); // Array count
        $this->assertTrue($rule->validate('field', 5.0, [])); // float value
    }

    public function testInvalidValues(): void
    {
        $rule = new MinRule('5');
        $this->assertFalse($rule->validate('field', 'abcd', [])); // String too short
        $this->assertFalse($rule->validate('field', 4, []));        // Number too small
        $this->assertFalse($rule->validate('field', [1, 2, 3], [])); // Array too small
        $this->assertFalse($rule->validate('field', '', []));       // Empty string
        $this->assertFalse($rule->validate('field', [], []));       // Empty array
    }

    public function testGetMessage(): void
    {
        $rule = new MinRule('5');
        // FIX: Update expected message to match the rule's new output
        $this->assertEquals('The username must be at least 5 characters, items, or value.', $rule->getMessage('username'));
    }
}