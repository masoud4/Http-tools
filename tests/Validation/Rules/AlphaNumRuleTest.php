<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\AlphaNumRule;

class AlphaNumRuleTest extends TestCase
{
    private AlphaNumRule $rule;

    protected function setUp(): void
    {
        $this->rule = new AlphaNumRule();
    }

    public function testValidAlphaNumStrings(): void
    {
        $this->assertTrue($this->rule->validate('field', 'abc123DEF', []));
        $this->assertTrue($this->rule->validate('field', 'justletters', []));
        $this->assertTrue($this->rule->validate('field', '12345', []));
        $this->assertTrue($this->rule->validate('field', 'a1', []));
    }

    public function testInvalidAlphaNumStrings(): void
    {
        $this->assertFalse($this->rule->validate('field', 'abc def', [])); // Space
        $this->assertFalse($this->rule->validate('field', 'abc!', []));    // Special char
        $this->assertFalse($this->rule->validate('field', '你好', []));    // Non-ASCII
        $this->assertFalse($this->rule->validate('field', '', []));        // Empty string
    }

    public function testNonScalarValues(): void // Renamed from testNonStringValues
    {
        // ctype_alnum casts integers and floats to string.
        // For floats, it will fail if it contains a decimal point,
        // so 12.34 becomes "12.34", which is not alphanumeric.
        $this->assertTrue($this->rule->validate('field', 123, []));       // Integer should pass
        $this->assertFalse($this->rule->validate('field', 12.34, []));    // Float should fail (due to '.')
        $this->assertFalse($this->rule->validate('field', [], []));       // Array
        $this->assertFalse($this->rule->validate('field', null, []));     // Null
        $this->assertFalse($this->rule->validate('field', true, []));     // Boolean
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The username field must only contain letters and numbers.', $this->rule->getMessage('username'));
    }
}