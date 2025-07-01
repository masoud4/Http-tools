<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\SameRule;

class SameRuleTest extends TestCase
{
    public function testValuesAreSame(): void
    {
        $rule = new SameRule('field2');
        $data = ['field1' => 'value', 'field2' => 'value'];
        $this->assertTrue($rule->validate('field1', 'value', $data));

        $data = ['field1' => 123, 'field2' => 123];
        $this->assertTrue($rule->validate('field1', 123, $data));

        $data = ['field1' => '1', 'field2' => '1'];
        $this->assertTrue($rule->validate('field1', '1', $data));

        $data = ['field1' => true, 'field2' => true];
        $this->assertTrue($rule->validate('field1', true, $data));
    }

    public function testValuesAreDifferent(): void
    {
        $rule = new SameRule('field2');
        $data = ['field1' => 'value1', 'field2' => 'value2'];
        $this->assertFalse($rule->validate('field1', 'value1', $data));

        $data = ['field1' => 1, 'field2' => '1']; // Different due to strict comparison
        $this->assertFalse($rule->validate('field1', 1, $data));

        $data = ['field1' => true, 'field2' => false];
        $this->assertFalse($rule->validate('field1', true, $data));
    }

    public function testOtherFieldDoesNotExist(): void
    {
        $rule = new SameRule('non_existent_field');
        $data = ['field1' => 'value'];
        // If the other field doesn't exist, the comparison cannot be made, so it fails.
        $this->assertFalse($rule->validate('field1', 'value', $data));
    }

    public function testGetMessage(): void
    {
        $rule = new SameRule('password');
        $this->assertEquals('The password_confirmation field must match password.', $rule->getMessage('password_confirmation'));
    }
}