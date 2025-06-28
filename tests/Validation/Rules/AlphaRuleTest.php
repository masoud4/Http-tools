<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\AlphaRule;

class AlphaRuleTest extends TestCase
{
    private AlphaRule $rule;

    protected function setUp(): void
    {
        $this->rule = new AlphaRule();
    }

    public function testValidAlphaStrings(): void
    {
        $this->assertTrue($this->rule->validate('field', 'abcdef', []));
        $this->assertTrue($this->rule->validate('field', 'ABCDEF', []));
        $this->assertTrue($this->rule->validate('field', 'MixedCase', []));
    }

    public function testInvalidAlphaStrings(): void
    {
        $this->assertFalse($this->rule->validate('field', 'abc 123', [])); // Space
        $this->assertFalse($this->rule->validate('field', 'abc123', []));  // Numbers
        $this->assertFalse($this->rule->validate('field', 'abc!', []));    // Special char
        $this->assertFalse($this->rule->validate('field', '', []));        // Empty string
        $this->assertFalse($this->rule->validate('field', '你好', []));    // Non-ASCII
    }

    public function testNonStringValues(): void
    {
        $this->assertFalse($this->rule->validate('field', 123, []));
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The name field must only contain letters.', $this->rule->getMessage('name'));
    }
}