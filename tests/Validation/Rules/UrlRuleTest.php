<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Validation\Rules\UrlRule;

class UrlRuleTest extends TestCase
{
    private UrlRule $rule;

    protected function setUp(): void
    {
        $this->rule = new UrlRule();
    }

    public function testValidUrls(): void
    {
        $this->assertTrue($this->rule->validate('field', 'http://example.com', []));
        $this->assertTrue($this->rule->validate('field', 'https://www.example.com/path?query=1#fragment', []));
        $this->assertTrue($this->rule->validate('field', 'ftp://ftp.test.org', []));
        $this->assertTrue($this->rule->validate('field', 'http://localhost', []));
    }

    public function testInvalidUrls(): void
    {
        $this->assertFalse($this->rule->validate('field', 'not-a-url', []));
        $this->assertFalse($this->rule->validate('field', 'www.example.com', [])); // Missing scheme
        $this->assertFalse($this->rule->validate('field', 'http://', []));
        $this->assertFalse($this->rule->validate('field', '', [])); // Empty string
        $this->assertFalse($this->rule->validate('field', null, [])); // Null
    }

    public function testNonStringValues(): void
    {
        $this->assertFalse($this->rule->validate('field', 123, []));
        $this->assertFalse($this->rule->validate('field', [], []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The website field must be a valid URL.', $this->rule->getMessage('website'));
    }
}