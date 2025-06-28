Classic/HttpTools
A simple yet effective PHP library for handling HTTP requests, responses, and data validation in classic PHP applications. This library provides foundational tools to manage incoming requests, craft outgoing responses, and validate user input with a flexible rule-based system.

Features
Request Handling: Parse GET, POST, JSON, and file uploads. Access headers and request method.

Response Management: Set HTTP status codes, headers, send content (HTML or JSON), and handle redirects with optional notifications.

Data Validation: A robust Validator class with a comprehensive set of built-in rules (required, email, string, int, min, max, array, boolean, date, regex, etc.).

Conditional Validation: required_if rule to make fields conditionally mandatory.

Custom Rules: Easily extend the validator with your own custom validation logic.

Error Bag: Centralized error management for easy display of validation messages.

Usage
Request Handling (Classic\HttpTools\Http\Request)
<?php

// Assuming you have your autoloader or class loading mechanism set up
// require_once 'path/to/Classic/HttpTools/Http/Request.php'; 
// require_once 'path/to/Classic/HttpTools/Http/Response.php'; 
// etc.

use Classic\HttpTools\Http\Request;

// In a real application, Request is typically instantiated once:
// $request = new Request();

// For demonstration/testing, you can inject superglobals:
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/users?id=123';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer my_token';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_POST = ['name' => 'LegacyPostData']; // If content-type is not application/json
$requestBody = '{"username": "john.doe", "email": "john@example.com"}';

$request = new Request($_GET, $_POST, $_SERVER, $requestBody, $_FILES);


// Get request method
echo "Method: " . $request->method() . PHP_EOL; // Output: Method: POST

// Get URI
echo "URI: " . $request->uri() . PHP_EOL; // Output: URI: /api/users?id=123

// Access input data (JSON takes precedence over POST, POST over GET)
echo "Username (input): " . $request->input('username') . PHP_EOL; // Output: Username (input): john.doe
echo "Email (input): " . $request->input('email') . PHP_EOL;       // Output: Email (input): john@example.com
echo "ID (query): " . $request->query('id') . PHP_EOL;             // Output: ID (query): 123

// Get all input data (merged GET, POST, JSON)
print_r($request->all());
/*
Output:
Array
(
    [id] => 123
    [name] => LegacyPostData
    [username] => john.doe
    [email] => john@example.com
)
*/

// Get specific JSON data
print_r($request->json());
/*
Output:
Array
(
    [username] => john.doe
    [email] => john@example.com
)
*/

// Get a specific header
echo "Authorization Header: " . $request->header('Authorization') . PHP_EOL; // Output: Authorization Header: Bearer my_token

// Check request type
if ($request->isPost()) {
    echo "This is a POST request." . PHP_EOL; // Output: This is a POST request.
}

Response Management (Classic\HttpTools\Http\Response)
<?php

// Assuming you have your autoloader or class loading mechanism set up

use Classic\HttpTools\Http\Response;

// Create a new Response object
$response = new Response();

// Set content and status
$response->content('<h1>Hello, World!</h1>')
         ->status(200)
         ->header('X-App-Version', '1.0');

// Add a notification (e.g., for flash messages on redirect)
$response->addNotification('Operation completed successfully!', 'success');

// Example: Sending an HTML response (in real app, this ends script execution)
// $response->send();

// Example: Sending a JSON response (in real app, this ends script execution)
// $response->json(['message' => 'Data received!', 'data' => ['id' => 1]], 201);

// Example: Redirecting (in real app, this ends script execution)
// $response->redirect('/dashboard', 302);

// Redirect with notifications (will be appended to URL as _notifications query param)
// $response->redirect('/profile', 302, ['user_message' => 'Profile updated']);

Data Validation (Classic\HttpTools\Validation\Validator)
<?php

// Assuming you have your autoloader or class loading mechanism set up

use Classic\HttpTools\Validation\Validator;

// Example data from a request
$data = [
    'username' => 'john_doe',
    'email' => 'invalid-email',
    'password' => 'Pass123!',
    'password_confirmation' => 'Pass123!',
    'age' => 25,
    'tags' => ['php', 'web'],
    'homepage' => 'invalid-url',
    'delivery_option' => 'ship',
    'shipping_address' => '', // Will be required if delivery_option is 'ship'
    'avatar' => [
        'name' => 'profile.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/phpfile',
        'error' => UPLOAD_ERR_OK,
        'size' => 500000 // 500KB
    ]
];

// Define validation rules
$rules = [
    'username' => 'required|string|min:3|max:20|alpha_num',
    'email' => 'required|email',
    'password' => 'required|string|min:8|regex:^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$',
    'password_confirmation' => 'required|same:password',
    'age' => 'required|int|between:18,99',
    'tags' => 'array|min:1',
    'homepage' => 'url', // Optional URL, will only validate if not empty
    'delivery_option' => 'required|in:ship,pickup',
    'shipping_address' => 'required_if:delivery_option,ship|string|min:10',
    'avatar' => function($field, $value, $data) {
        // Example of an inline Closure rule for file uploads
        if (!isset($value['error']) || $value['error'] !== UPLOAD_ERR_OK) {
            return "The {$field} upload failed.";
        }
        if ($value['size'] > 1024 * 1024) { // Max 1MB
            return "The {$field} size must not exceed 1MB.";
        }
        if (!in_array($value['type'], ['image/jpeg', 'image/png'])) {
            return "The {$field} must be a JPEG or PNG image.";
        }
        return true; // Validation passes
    },
];

// Define custom messages (optional)
$messages = [
    'email.required' => 'We really need your email address.',
    'email.email' => 'That does not look like a valid email.',
    'password.regex' => 'Password must be at least 8 characters, include uppercase, lowercase, number, and special character.',
];

$validator = new Validator($data);
$validator->setRules($rules);
$validator->setMessages($messages);

if ($validator->validate()) {
    echo "Validation passed!" . PHP_EOL;
    print_r($validator->validatedData());
} else {
    echo "Validation failed!" . PHP_EOL;
    print_r($validator->errors()->all());
    echo "First email error: " . $validator->errors()->first('email') . PHP_EOL;
}

// Example with a passing scenario (assuming previous data adjusted)
$dataPassing = [
    'username' => 'testuser1',
    'email' => 'test@example.com',
    'password' => 'SecureP@ss1',
    'password_confirmation' => 'SecureP@ss1',
    'age' => 30,
    'tags' => ['a'],
    'homepage' => 'https://www.example.com',
    'delivery_option' => 'pickup', // Now shipping_address is optional
    'shipping_address' => '',
    'avatar' => [
        'name' => 'profile.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/phpfile',
        'error' => UPLOAD_ERR_NO_FILE, // No file uploaded, which is fine if not required
        'size' => 0
    ]
];

$validatorPassing = new Validator($dataPassing);
$validatorPassing->setRules($rules);
if ($validatorPassing->validate()) {
    echo "\nValidation passed for passing data!" . PHP_EOL;
    print_r($validatorPassing->validatedData());
} else {
    echo "\nValidation failed for passing data (unexpected)!" . PHP_EOL;
    print_r($validatorPassing->errors()->all());
}

Extending the Validator with Custom Rules
You can add your own reusable validation rules using Validator::extend(). Your custom rule class must implement Classic\HttpTools\Validation\ValidationRuleInterface.

Create your custom rule class (e.g., src/Validation/Rules/EvenNumberRule.php):

<?php
// src/Validation/Rules/EvenNumberRule.php
namespace Classic\HttpTools\Validation\Rules;

use Classic\HttpTools\Validation\ValidationRuleInterface;

class EvenNumberRule implements ValidationRuleInterface
{
    public function __construct(?string $param = null)
    {
        // Constructor can take a parameter if your rule needs one (e.g., 'even:strict')
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        if (!is_numeric($value)) {
            return false; // Not a number
        }
        return (int)$value % 2 === 0; // Check if it's an even integer
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field must be an even number.";
    }
}

Register the custom rule (e.g., in your index.php or bootstrap file):

<?php
// Assuming you have your autoloader or class loading mechanism set up

use Classic\HttpTools\Validation\Validator;
use Classic\HttpTools\Validation\Rules\EvenNumberRule;

Validator::extend('even', EvenNumberRule::class);

// Now you can use 'even' in your validation rules:
$data = ['number' => 10];
$rules = ['number' => 'required|int|even'];

$validator = new Validator($data);
$validator->setRules($rules);
if ($validator->validate()) {
    echo "Number is even and valid!" . PHP_EOL;
} else {
    print_r($validator->errors()->all());
}

$data = ['number' => 7];
$validator = new Validator($data);
$validator->setRules($rules);
if (!$validator->validate()) {
    echo "Number is odd and invalid, as expected:" . PHP_EOL;
    print_r($validator->errors()->all()); // Output: The number field must be an even number.
}
