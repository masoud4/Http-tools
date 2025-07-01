<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\RequiredRule;

class RequiredRuleTest extends TestCase
{
    private RequiredRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RequiredRule();
    }

    public function testValidNonEmptyValues(): void
    {
        $this->assertTrue($this->rule->validate('field', 'hello', []));
        $this->assertTrue($this->rule->validate('field', 123, []));
        $this->assertTrue($this->rule->validate('field', 0, [])); // 0 is not empty
        $this->assertTrue($this->rule->validate('field', true, []));
        $this->assertTrue($this->rule->validate('field', ['a'], []));
        $this->assertTrue($this->rule->validate('field', ['key' => 'value'], []));
        // REMOVED: $this->assertTrue($this->rule->validate('field', ' ', []));
        // Because the trimming happens in the Validator, not in the rule itself.
    }

    public function testInvalidEmptyValues(): void
    {
        $this->assertFalse($this->rule->validate('field', '', []));
        $this->assertFalse($this->rule->validate('field', null, []));
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', '   ', [])); // String with only spaces
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The name field is required.', $this->rule->getMessage('name'));
    }
}