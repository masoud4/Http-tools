<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\InRule;

class InRuleTest extends TestCase
{
    public function testValidValues(): void
    {
        $rule = new InRule('red,green,blue');
        $this->assertTrue($rule->validate('field', 'red', []));
        $this->assertTrue($rule->validate('field', 'green', []));
        $this->assertTrue($rule->validate('field', 'blue', []));

        $rule = new InRule('1,2,3');
        $this->assertTrue($rule->validate('field', '1', []));
        $this->assertTrue($rule->validate('field', 2, [])); // Numeric value
    }

    public function testInvalidValues(): void
    {
        $rule = new InRule('red,green,blue');
        $this->assertFalse($rule->validate('field', 'yellow', []));
        $this->assertFalse($rule->validate('field', 'Red', [])); // Case sensitive with strict
        $this->assertFalse($rule->validate('field', '', []));
        $this->assertFalse($rule->validate('field', null, []));

        $rule = new InRule('1,2,3');
        $this->assertFalse($rule->validate('field', '4', []));
        $this->assertFalse($rule->validate('field', 0, []));
    }

    public function testGetMessage(): void
    {
        $rule = new InRule('option1,option2');
        $this->assertEquals('The selected color is invalid.', $rule->getMessage('color'));
    }
}