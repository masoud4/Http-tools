<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\StringTypeRule;

class StringTypeRuleTest extends TestCase
{
    private StringTypeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new StringTypeRule();
    }

    public function testValidStrings(): void
    {
        $this->assertTrue($this->rule->validate('field', 'hello', []));
        $this->assertTrue($this->rule->validate('field', '', []));
        $this->assertTrue($this->rule->validate('field', '123', [])); // Numeric string
    }

    public function testInvalidTypes(): void
    {
        $this->assertFalse($this->rule->validate('field', 123, []));
        $this->assertFalse($this->rule->validate('field', 12.34, []));
        $this->assertFalse($this->rule->validate('field', true, []));
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The name field must be a string.', $this->rule->getMessage('name'));
    }
}