<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\DateRule;

class DateRuleTest extends TestCase
{
    private DateRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DateRule();
    }

    public function testValidDateStrings(): void
    {
        $this->assertTrue($this->rule->validate('field', '2023-01-01', []));
        $this->assertTrue($this->rule->validate('field', '2024-02-29', [])); // Leap year
        $this->assertTrue($this->rule->validate('field', '1999/05/15', []));
        $this->assertTrue($this->rule->validate('field', '2023-11-20 14:30:00', [])); // With time
    }

    public function testInvalidDateStrings(): void
    {
        $this->assertFalse($this->rule->validate('field', 'not-a-date', []));
        $this->assertFalse($this->rule->validate('field', '2023-13-01', [])); // Invalid month
        $this->assertFalse($this->rule->validate('field', '2023-02-30', [])); // Invalid day for month
        $this->assertFalse($this->rule->validate('field', '', []));         // Empty string
        $this->assertFalse($this->rule->validate('field', ' ', []));        // Whitespace string
        $this->assertFalse($this->rule->validate('field', 'tomorrow', [])); // Relative date (too ambiguous for strict rule)
        $this->assertFalse($this->rule->validate('field', 'January 1st, 2023', [])); // Ambiguous format
        $this->assertFalse($this->rule->validate('field', '2023', [])); // Incomplete date
    }

    public function testNonStringValues(): void
    {
        $this->assertFalse($this->rule->validate('field', 12345, []));
        $this->assertFalse($this->rule->validate('field', [], []));
        $this->assertFalse($this->rule->validate('field', null, []));
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('The birth_date field must be a valid date.', $this->rule->getMessage('birth_date'));
    }
}