# 🚀 Classic/HttpTools

A robust and simple PHP library providing essential tools for HTTP request and response management, alongside a flexible and extendable data validation system for classic PHP applications. Designed for clarity, ease of use, and integration into existing projects without heavy frameworks.

---

[![Packagist Version](https://img.shields.io/packagist/v/classic/http-tools?style=flat-square&label=latest%20version)](https://packagist.org/packages/classic/http-tools)
[![Packagist Downloads](https://img.shields.io/packagist/dt/classic/http-tools?style=flat-square)](https://packagist.org/packages/classic/http-tools)
[![License](https://img.shields.io/packagist/l/classic/http-tools?style=flat-square)](LICENSE)
<!-- Add GitHub Actions/CI badge here if you set it up, e.g.:
[![Build Status](https://img.shields.io/github/actions/workflow/status/masoud4/Http-tools/main.yml?branch=main&style=flat-square)](https://github.com/masoud4/Http-tools/actions?query=workflow%3AMain)
-->

## ✨ Features

* **HTTP Request Handling:**
    * Effortlessly parse and access GET, POST, JSON, and file upload data.
    * Intuitive methods to retrieve request headers and the HTTP method.
    * Combines all input types with clear precedence (JSON > POST > GET).
* **HTTP Response Management:**
    * Fluent interface for setting HTTP status codes and custom headers.
    * Seamlessly send HTML or JSON responses.
    * Integrated redirection capabilities with optional notification passing.
* **Powerful Data Validation:**
    * A versatile `Validator` class with a rich set of built-in validation rules:
        * `required`, `email`, `string`, `int`, `min`, `max`, `array`, `boolean`, `date`, `url`, `ip`, `json`, `alpha`, `alpha_num`, `regex`.
        * `between`, `in`, `not_in`, `same`, `different`.
        * `size` for exact length/value/count matching.
    * **Conditional Validation:** Use `required_if` to make fields mandatory based on other input values.
    * **Custom Rules:** Extend the validator with your own bespoke validation logic using a simple interface.
    * **Centralized Error Bag:** Collect and retrieve all validation errors for easy display.

---

## 🚀 Installation

This library is designed for easy integration into any PHP project using [Composer](https://getcomposer.org/).

1.  **Add the package to your project:**

    ```bash
    composer require classic/http-tools
    ```

2.  **Optionally, for development and testing (highly recommended):**

    ```bash
    composer require --dev phpunit/phpunit
    ```

3.  **Ensure your `composer.json` is correctly configured:**

    ```json
    {
        "name": "classic/http-tools",
        "description": "A simple PHP library for HTTP request/response handling and data validation.",
        "type": "library",
        "license": "BSD-3-Clause",
        "keywords": ["http", "request", "response", "validation", "validator", "error-bag", "php"],
        "homepage": "[https://github.com/masoud4/Http-tools](https://github.com/masoud4/Http-tools)",
        "minimum-stability": "stable",
        "autoload": {
            "psr-4": {
                "Classic\\HttpTools\\": "src/"
            }
        },
        "autoload-dev": {
            "psr-4": {
                "Tests\\": "tests/"
            }
        },
        "authors": [
            {
                "name": "Masoud4",
                "email": "ashrafianpur.masoud@gmail.com",
                "homepage": "[https://your-personal-website.com](https://your-personal-website.com)",
                "role": "Developer"
            }
        ],
        "require": {
            "php": ">=8.0"
        },
        "require-dev": {
            "phpunit/phpunit": "^11.0"
        },
        "config": {
            "allow-plugins": {
                "php-http/discovery": true,
                "pestphp/pest-plugin": true
            }
        },
        "scripts": {
            "test": "vendor/bin/phpunit",
            "test-coverage": "vendor/bin/phpunit --coverage-html coverage"
        }
    }
    ```

4.  **Run Composer install/update to load dependencies:**

    ```bash
    composer install
    # or
    composer update
    ```

---

## 📚 Usage Examples

### 📥 Request Handling (`Classic\HttpTools\Http\Request`)

Parse incoming HTTP requests and access various input types seamlessly.

```php
<?php

// Include Composer's autoloader if not already done in your bootstrap
require_once 'vendor/autoload.php';

use Classic\HttpTools\Http\Request;

// Typically, in a web environment, Request is instantiated once without arguments,
// as it automatically reads from superglobals ($_GET, $_POST, $_SERVER, etc.).
// $request = new Request();

// For demonstration or testing purposes, you can manually inject values:
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/users?id=123&status=active';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer your_secret_token';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_POST = ['old_name' => 'LegacyPostData']; // This would be ignored by JSON content-type
$requestBody = '{"username": "john.doe", "email": "john@example.com", "preference": "dark"}';
$_FILES = [
    'profile_pic' => [
        'name' => 'mypic.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/php_upload_xyz',
        'error' => UPLOAD_ERR_OK,
        'size' => 123456
    ]
];

$request = new Request($_GET, $_POST, $_SERVER, $requestBody, $_FILES);

echo "--- Request Details ---" . PHP_EOL;
echo "Method: " . $request->method() . PHP_EOL; // Output: Method: POST
echo "URI: " . $request->uri() . PHP_EOL;       // Output: URI: /api/users?id=123&status=active

echo "\n--- Input Data ---" . PHP_EOL;
echo "Username (input method, JSON preferred): " . $request->input('username') . PHP_EOL; // Output: john.doe
echo "Email (input method): " . $request->input('email') . PHP_EOL;                     // Output: john@example.com
echo "ID (query string): " . $request->query('id') . PHP_EOL;                             // Output: 123
echo "Preference (JSON direct): " . $request->json('preference') . PHP_EOL;             // Output: dark
echo "Legacy POST data (post method): " . $request->post('old_name', 'N/A') . PHP_EOL;    // Output: N/A (because JSON content-type)

echo "\nAll Input Data (merged - JSON > POST > GET):" . PHP_EOL;
print_r($request->all());
/*
Output might look like:
Array
(
    [id] => 123
    [status] => active
    [old_name] => LegacyPostData
    [username] => john.doe
    [email] => john@example.com
    [preference] => dark
)
*/

echo "\n--- Headers ---" . PHP_EOL;
echo "Authorization Header: " . $request->header('Authorization') . PHP_EOL; // Output: Bearer your_secret_token
echo "Content-Type Header: " . $request->header('Content-Type') . PHP_EOL;   // Output: application/json

echo "\n--- File Uploads ---" . PHP_EOL;
print_r($request->file('profile_pic'));
/*
Output:
Array
(
    [name] => mypic.jpg
    [type] => image/jpeg
    [tmp_name] => /tmp/php_upload_xyz
    [error] => 0
    [size] => 123456
)
*/

if ($request->isPost()) {
    echo "\nThis is a POST request." . PHP_EOL;
}