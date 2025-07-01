<?php
namespace Tests\Errors;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Errors\ErrorBag;

class ErrorBagTest extends TestCase
{
    public function testAddAndHasErrors(): void
    {
        $errorBag = new ErrorBag();

        // Initially, no errors
        $this->assertFalse($errorBag->has());
        $this->assertFalse($errorBag->has('field1'));

        // Add a single error
        $errorBag->add('field1', 'Error message for field 1.');
        $this->assertTrue($errorBag->has());
        $this->assertTrue($errorBag->has('field1'));
        $this->assertFalse($errorBag->has('field2'));

        // Add another error for a different field
        $errorBag->add('field2', 'Error message for field 2.');
        $this->assertTrue($errorBag->has());
        $this->assertTrue($errorBag->has('field1'));
        $this->assertTrue($errorBag->has('field2'));

        // Add multiple errors for the same field
        $errorBag->add('field1', 'Another error for field 1.');
        $this->assertTrue($errorBag->has('field1'));
    }

    public function testGetErrors(): void
    {
        $errorBag = new ErrorBag();
        $errorBag->add('name', 'Name is required.');
        $errorBag->add('email', 'Email is invalid.');
        $errorBag->add('name', 'Name is too short.');

        // Get all errors
        $allErrors = $errorBag->get();
        $this->assertIsArray($allErrors);
        $this->assertArrayHasKey('name', $allErrors);
        $this->assertArrayHasKey('email', $allErrors);
        $this->assertCount(2, $allErrors); // Two fields with errors

        // Get errors for a specific field
        $nameErrors = $errorBag->get('name');
        $this->assertIsArray($nameErrors);
        $this->assertCount(2, $nameErrors);
        $this->assertEquals(['Name is required.', 'Name is too short.'], $nameErrors);

        $emailErrors = $errorBag->get('email');
        $this->assertIsArray($emailErrors);
        $this->assertCount(1, $emailErrors);
        $this->assertEquals(['Email is invalid.'], $emailErrors);

        // Get errors for a non-existent field
        $nonExistentErrors = $errorBag->get('non_existent_field');
        $this->assertIsArray($nonExistentErrors);
        $this->assertEmpty($nonExistentErrors);
    }

    public function testGetFirstError(): void
    {
        $errorBag = new ErrorBag();
        $errorBag->add('name', 'Name is required.');
        $errorBag->add('name', 'Name is too short.');
        $errorBag->add('email', 'Email is invalid.');

        $this->assertEquals('Name is required.', $errorBag->first('name'));
        $this->assertEquals('Email is invalid.', $errorBag->first('email'));
        $this->assertNull($errorBag->first('non_existent_field'));
    }

    public function testAllErrorsAsFlatArray(): void
    {
        $errorBag = new ErrorBag();
        $errorBag->add('name', 'Name is required.');
        $errorBag->add('email', 'Email is invalid.');
        $errorBag->add('name', 'Name is too short.');

        $expected = [
            'Name is required.',
            'Name is too short.',
            'Email is invalid.',
        ];
        // Order might not be guaranteed across different PHP versions for array_merge or iteration order
        // So, sort both for comparison
        $actual = $errorBag->all();
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testToJson(): void
    {
        $errorBag = new ErrorBag();
        $errorBag->add('username', 'The username is required.');
        $errorBag->add('username', 'The username must be at least 5 characters.');
        $errorBag->add('email', 'The email must be a valid email address.');

        $expectedJson = json_encode([
            'username' => [
                'The username is required.',
                'The username must be at least 5 characters.'
            ],
            'email' => [
                'The email must be a valid email address.'
            ]
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $errorBag->toJson());
    }

    public function testEmptyErrorBagToJson(): void
    {
        $errorBag = new ErrorBag();
        $this->assertJsonStringEqualsJsonString('{}', $errorBag->toJson());
    }
}