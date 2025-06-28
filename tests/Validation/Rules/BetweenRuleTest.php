<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\BetweenRule;

class BetweenRuleTest extends TestCase
{
    public function testValidNumbers(): void
    {
        $rule = new BetweenRule('10,20');
        $this->assertTrue($rule->validate('field', 10, []));
        $this->assertTrue($rule->validate('field', 15, []));
        $this->assertTrue($rule->validate('field', 20, []));
        $this->assertTrue($rule->validate('field', '10', [])); // String numeric
        $this->assertTrue($rule->validate('field', '15.5', [])); // String float
    }

    public function testInvalidNumbers(): void
    {
        $rule = new BetweenRule('10,20');
        $this->assertFalse($rule->validate('field', 9, []));
        $this->assertFalse($rule->validate('field', 21, []));
        $this->assertFalse($rule->validate('field', '9.9', []));
        $this->assertFalse($rule->validate('field', '20.1', []));
    }

    public function testNonNumericValues(): void
    {
        $rule = new BetweenRule('10,20');
        $this->assertFalse($rule->validate('field', 'abc', []));
        $this->assertFalse($rule->validate('field', null, []));
        $this->assertFalse($rule->validate('field', [], []));
        $this->assertFalse($rule->validate('field', true, []));
    }

    public function testGetMessage(): void
    {
        $rule = new BetweenRule('18,60');
        $this->assertEquals('The age field must be between 18 and 60.', $rule->getMessage('age'));
    }
}