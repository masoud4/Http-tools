<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\IpRule;

class IpRuleTest extends TestCase
{
    private IpRule $rule;

    protected function setUp(): void
    {
        $this->rule = new IpRule();
    }

    public function testValidIpAddresses(): void
    {
        $this->assertTrue($this->rule->validate('field', '192.168.1.1', []));
        $this->assertTrue($this->rule->validate('field', '10.0.0.255', []));
        $this->assertTrue($this->rule->validate('field', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', [])); // IPv6
        $this->assertTrue($this->rule->validate('field', '::1', [])); // IPv6 loopback
    }

    public function testInvalidIpAddresses(): void
    {
        $this->assertFalse($this->rule->validate('field', '256.1.1.1', [])); // Out of range
        $this->assertFalse($this->rule->validate('field', '192.168.1', []));  // Incomplete
        $this->assertFalse($this->rule->validate('field', 'not.an.ip', []));
        $this->assertFalse($this->rule->validate('field', '192.168.1.1.1', []));
        $this->assertFalse($this->rule->validate('field', '', []));
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The server_ip field must be a valid IP address.', $this->rule->getMessage('server_ip'));
    }
}