<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\ArrayTypeRule;

class ArrayTypeRuleTest extends TestCase
{
    private ArrayTypeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new ArrayTypeRule();
    }

    public function testValidArrays(): void
    {
        $this->assertTrue($this->rule->validate('field', [], []));
        $this->assertTrue($this->rule->validate('field', [1, 2, 3], []));
        $this->assertTrue($this->rule->validate('field', ['key' => 'value'], []));
    }

    public function testInvalidTypes(): void
    {
        $this->assertFalse($this->rule->validate('field', 'string', []));
        $this->assertFalse($this->rule->validate('field', 123, []));
        $this->assertFalse($this->rule->validate('field', 12.34, []));
        $this->assertFalse($this->rule->validate('field', true, []));
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The tags field must be an array.', $this->rule->getMessage('tags'));
    }
}