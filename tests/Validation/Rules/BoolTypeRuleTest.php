<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\BoolTypeRule;

class BoolTypeRuleTest extends TestCase
{
    private BoolTypeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new BoolTypeRule();
    }

    public function testValidBooleans(): void
    {
        $this->assertTrue($this->rule->validate('field', true, []));
        $this->assertTrue($this->rule->validate('field', false, []));
    }

    public function testValidBooleanStringsAndNumbers(): void
    {
        $this->assertTrue($this->rule->validate('field', 'true', []));
        $this->assertTrue($this->rule->validate('field', 'false', []));
        $this->assertTrue($this->rule->validate('field', '1', []));
        $this->assertTrue($this->rule->validate('field', '0', []));
        $this->assertTrue($this->rule->validate('field', 1, []));
        $this->assertTrue($this->rule->validate('field', 0, []));
        $this->assertTrue($this->rule->validate('field', 'yes', []));
        $this->assertTrue($this->rule->validate('field', 'no', []));
        $this->assertTrue($this->rule->validate('field', 'TRUE', [])); // Case insensitivity
        $this->assertTrue($this->rule->validate('field', 'FALSE', []));
    }

    public function testInvalidTypes(): void
    {
        $this->assertFalse($this->rule->validate('field', 'some string', []));
        $this->assertFalse($this->rule->validate('field', 123, [])); // Not 0 or 1
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', null, []));
        $this->assertFalse($this->rule->validate('field', 0.5, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The newsletter field must be a boolean (true/false, 1/0).', $this->rule->getMessage('newsletter'));
    }
}