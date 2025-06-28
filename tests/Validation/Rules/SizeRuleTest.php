<?php
namespace Tests\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Classic\HttpTools\Validation\Rules\SizeRule;

class SizeRuleTest extends TestCase
{
    public function testValidValues(): void
    {
        $rule = new SizeRule('5'); // The rule expects a size of 5

        // Echo the exact validation result for the problematic case
        $resultForNumericString5 = $rule->validate('field', '5', []);
        // echo "Result for '5' in SizeRuleTest: " . ($resultForNumericString5 ? "TRUE" : "FALSE") . PHP_EOL; // Line 13 in your environment may shift slightly
        $this->assertTrue($resultForNumericString5); // This is the line that's failing (Line 15 in your last report)

        $this->assertTrue($rule->validate('field', 'abcde', []));      // String length exactly 5
        $this->assertTrue($rule->validate('field', 5, []));            // Integer value exactly 5
        $this->assertTrue($rule->validate('field', [1, 2, 3, 4, 5], [])); // Array count exactly 5
        $this->assertTrue($rule->validate('field', 5.0, []));          // Float value exactly 5.0
    }

    public function testInvalidValues(): void
    {
        $rule = new SizeRule('5');

        $this->assertFalse($rule->validate('field', 'abcd', []));       // String length 4 (too short)
        $this->assertFalse($rule->validate('field', 'abcdef', []));    // String length 6 (too long)
        $this->assertFalse($rule->validate('field', 4, []));            // Numeric value 4 (too small)
        $this->assertFalse($rule->validate('field', 6, []));            // Numeric value 6 (too large)
        $this->assertFalse($rule->validate('field', [1, 2, 3], []));    // Array count 3 (too small)
    }

    public function testGetMessage(): void
    {
        $rule = new SizeRule('10');
        $this->assertEquals('The quantity field must be 10 characters, items, or value.', $rule->getMessage('quantity'));
    }
}