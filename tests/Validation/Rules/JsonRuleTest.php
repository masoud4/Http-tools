<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\JsonRule;

class JsonRuleTest extends TestCase
{
    private JsonRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonRule();
    }

    public function testValidJsonStrings(): void
    {
        $this->assertTrue($this->rule->validate('field', '{"key": "value"}', []));
        $this->assertTrue($this->rule->validate('field', '[]', []));
        $this->assertTrue($this->rule->validate('field', '123', [])); // Valid JSON number
        $this->assertTrue($this->rule->validate('field', '"hello"', [])); // Valid JSON string
        $this->assertTrue($this->rule->validate('field', 'true', [])); // Valid JSON boolean
        $this->assertTrue($this->rule->validate('field', 'null', [])); // Valid JSON null
    }

    public function testInvalidJsonStrings(): void
    {
        $this->assertFalse($this->rule->validate('field', '{"key": "value",}', [])); // Trailing comma
        $this->assertFalse($this->rule->validate('field', '{"key": "value"', []));   // Unclosed object
        $this->assertFalse($this->rule->validate('field', 'abc', []));             // Not JSON
        $this->assertFalse($this->rule->validate('field', '', []));                // Empty string
    }

    public function testNonStringValues(): void
    {
        $this->assertFalse($this->rule->validate('field', ['key' => 'value'], [])); // Array, not JSON string
        $this->assertFalse($this->rule->validate('field', 123, []));              // Int, not JSON string
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The settings field must be a valid JSON string.', $this->rule->getMessage('settings'));
    }
}