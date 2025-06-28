<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\NotInRule;

class NotInRuleTest extends TestCase
{
    public function testValidValues(): void
    {
        $rule = new NotInRule('admin,super_user');
        $this->assertTrue($rule->validate('field', 'guest', []));
        $this->assertTrue($rule->validate('field', 'moderator', []));

        $rule = new NotInRule('10,20');
        $this->assertTrue($rule->validate('field', 5, []));
        $this->assertTrue($rule->validate('field', '30', []));
    }

    public function testInvalidValues(): void
    {
        $rule = new NotInRule('admin,super_user');
        $this->assertFalse($rule->validate('field', 'admin', []));
        $this->assertFalse($rule->validate('field', 'super_user', []));

        $rule = new NotInRule('10,20');
        $this->assertFalse($rule->validate('field', 10, []));
        $this->assertFalse($rule->validate('field', '20', []));
    }

    public function testGetMessage(): void
    {
        $rule = new NotInRule('forbidden,restricted');
        $this->assertEquals('The selected role is invalid.', $rule->getMessage('role'));
    }
}