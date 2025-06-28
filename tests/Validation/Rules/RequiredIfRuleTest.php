<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\RequiredIfRule;

class RequiredIfRuleTest extends TestCase
{
    public function testRequiredIfConditionMetAndValuePresent(): void
    {
        $rule = new RequiredIfRule('delivery_option,ship');
        $data = ['delivery_option' => 'ship', 'shipping_address' => '123 Main St'];
        $this->assertTrue($rule->validate('shipping_address', '123 Main St', $data));
    }

    public function testRequiredIfConditionMetAndValueMissing(): void
    {
        $rule = new RequiredIfRule('delivery_option,ship');
        $data = ['delivery_option' => 'ship', 'shipping_address' => ''];
        $this->assertFalse($rule->validate('shipping_address', '', $data));

        $data = ['delivery_option' => 'ship']; // Field not even present
        $this->assertFalse($rule->validate('shipping_address', null, $data));
    }

    public function testRequiredIfConditionNotMetAndValueMissing(): void
    {
        $rule = new RequiredIfRule('delivery_option,ship');
        $data = ['delivery_option' => 'pickup', 'shipping_address' => '']; // Not required
        $this->assertTrue($rule->validate('shipping_address', '', $data));

        $data = ['delivery_option' => 'pickup']; // Field not even present
        $this->assertTrue($rule->validate('shipping_address', null, $data));
    }

    public function testRequiredIfConditionNotMetAndValuePresent(): void
    {
        $rule = new RequiredIfRule('delivery_option,ship');
        $data = ['delivery_option' => 'pickup', 'shipping_address' => 'Some address present'];
        $this->assertTrue($rule->validate('shipping_address', 'Some address present', $data));
    }

    public function testOtherFieldMissing(): void
    {
        $rule = new RequiredIfRule('non_existent_option,value');
        $data = ['field_to_check' => ''];
        // If the other field doesn't exist, the condition for required_if is not met.
        // So the field is not required.
        $this->assertTrue($rule->validate('field_to_check', '', $data));
        $this->assertTrue($rule->validate('field_to_check', 'some value', $data));
    }

    public function testGetMessage(): void
    {
        $rule = new RequiredIfRule('status,active');
        $this->assertEquals('The comments field is required when status is active.', $rule->getMessage('comments'));
    }
}