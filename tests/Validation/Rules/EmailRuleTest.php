<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\EmailRule;

class EmailRuleTest extends TestCase
{
    private EmailRule $rule;

    protected function setUp(): void
    {
        $this->rule = new EmailRule();
    }

    public function testValidEmails(): void
    {
        $this->assertTrue($this->rule->validate('field', 'test@example.com', []));
        $this->assertTrue($this->rule->validate('field', 'firstname.lastname@sub.domain.co.uk', []));
        $this->assertTrue($this->rule->validate('field', 'user+tag@domain.net', []));
    }

    public function testInvalidEmails(): void
    {
        $this->assertFalse($this->rule->validate('field', 'invalid-email', []));
        $this->assertFalse($this->rule->validate('field', 'test@.com', []));
        $this->assertFalse($this->rule->validate('field', 'test@example', []));
        $this->assertFalse($this->rule->validate('field', '@example.com', []));
        $this->assertFalse($this->rule->validate('field', 'test@example..com', []));
        $this->assertFalse($this->rule->validate('field', '', [])); // Empty string
        $this->assertFalse($this->rule->validate('field', null, [])); // Null
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The email field must be a valid email address.', $this->rule->getMessage('email'));
    }
}