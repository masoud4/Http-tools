<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\IntTypeRule;

class IntTypeRuleTest extends TestCase
{
    private IntTypeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new IntTypeRule();
    }

    public function testValidIntegers(): void
    {
        $this->assertTrue($this->rule->validate('field', 123, []));
        $this->assertTrue($this->rule->validate('field', 0, []));
        $this->assertTrue($this->rule->validate('field', -45, []));
        $this->assertTrue($this->rule->validate('field', '123', [])); // String integer
        $this->assertTrue($this->rule->validate('field', '0', []));   // String zero
        $this->assertTrue($this->rule->validate('field', '-45', [])); // String negative integer
    }

    public function testInvalidIntegers(): void
    {
        $this->assertFalse($this->rule->validate('field', 'abc', []));
        $this->assertFalse($this->rule->validate('field', 12.34, []));
        $this->assertFalse($this->rule->validate('field', '12.34', []));
        $this->assertFalse($this->rule->validate('field', true, []));
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', null, []));
        $this->assertFalse($this->rule->validate('field', '', [])); // Empty string
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The age field must be an integer.', $this->rule->getMessage('age'));
    }
}