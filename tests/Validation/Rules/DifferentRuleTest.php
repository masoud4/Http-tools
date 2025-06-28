<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\DifferentRule;

class DifferentRuleTest extends TestCase
{
    public function testValuesAreDifferent(): void
    {
        $rule = new DifferentRule('field2');
        $data = ['field1' => 'value1', 'field2' => 'value2'];
        $this->assertTrue($rule->validate('field1', 'value1', $data));

        $data = ['field1' => 1, 'field2' => '1']; // Different due to strict comparison
        $this->assertTrue($rule->validate('field1', 1, $data));

        $data = ['field1' => true, 'field2' => false];
        $this->assertTrue($rule->validate('field1', true, $data));
    }

    public function testValuesAreSame(): void
    {
        $rule = new DifferentRule('field2');
        $data = ['field1' => 'value', 'field2' => 'value'];
        $this->assertFalse($rule->validate('field1', 'value', $data));

        $data = ['field1' => 123, 'field2' => 123];
        $this->assertFalse($rule->validate('field1', 123, $data));

        $data = ['field1' => '1', 'field2' => '1'];
        $this->assertFalse($rule->validate('field1', '1', $data));

        $data = ['field1' => true, 'field2' => true];
        $this->assertFalse($rule->validate('field1', true, $data));
    }

    public function testOtherFieldDoesNotExist(): void
    {
        $rule = new DifferentRule('non_existent_field');
        $data = ['field1' => 'value'];
        // Rule passes if the comparison field doesn't exist, as there's no value to be the same as.
        $this->assertTrue($rule->validate('field1', 'value', $data));
    }

    public function testGetMessage(): void
    {
        $rule = new DifferentRule('password');
        $this->assertEquals('The password_confirmation field must be different from password.', $rule->getMessage('password_confirmation'));
    }
}