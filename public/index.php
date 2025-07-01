<?php

require_once __DIR__ . '/../vendor/autoload.php';

use masoud4\HttpTools\Http\Request;
use masoud4\HttpTools\Http\Response;
use masoud4\HttpTools\Validation\Validator;
use masoud4\HttpTools\Validation\ValidationRuleInterface; // For custom class rule example

// --- Helper to display errors and notifications ---
function displayMessages(Response $response, array $errors = []): string {
    $html = '';

    // Check for notifications passed via query string (from redirect)
    if (isset($_GET['_notifications'])) {
        $passedNotifications = json_decode($_GET['_notifications'], true) ?? [];
        foreach ($passedNotifications as $notification) {
            $type = htmlspecialchars($notification['type']);
            $message = htmlspecialchars($notification['message']);
            $html .= "<div style='padding: 10px; margin-bottom: 10px; border-radius: 5px; color: white; background-color: " . ($type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : ($type === 'warning' ? '#ffc107' : '#007bff'))) . ";'>{$message}</div>";
        }
    }

    // Check for notifications set directly on the current response
    foreach ($response->getNotifications() as $notification) {
        $type = htmlspecialchars($notification['type']);
        $message = htmlspecialchars($notification['message']);
        $html .= "<div style='padding: 10px; margin-bottom: 10px; border-radius: 5px; color: white; background-color: " . ($type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : ($type === 'warning' ? '#ffc107' : '#007bff'))) . ";'>{$message}</div>";
    }

    // Display validation errors
    if (!empty($errors)) {
        $html .= "<div style='background-color: #ffe0e6; border: 1px solid #ff0033; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        $html .= "<h3 style='color: #ff0033; margin-top: 0;'>Validation Errors:</h3><ul>";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $html .= "<li style='color: #ff0033;'><strong>" . htmlspecialchars($field) . ":</strong> " . htmlspecialchars($error) . "</li>";
            }
        }
        $html .= "</ul></div>";
    }
    return $html;
}

// --- Custom Validation Rule Example (Class-based) ---
// This rule checks if a number is even
class EvenNumberRule implements ValidationRuleInterface {
    public function __construct(?string $param = null) {}
    public function validate(string $field, mixed $value, array $data): bool {
        return is_numeric($value) && (int)$value % 2 === 0;
    }
    public function getMessage(string $field): string {
        return "The {$field} must be an even number.";
    }
}
// Register the custom rule globally
Validator::extend('even', EvenNumberRule::class);


// --- Request/Response/Validation Logic ---
$request = new Request();
$response = new Response();

$uriPath = parse_url($request->uri(), PHP_URL_PATH);

if ($uriPath === '/' && $request->method() === 'GET') {
    $notificationsHtml = displayMessages($response); // Check for notifications from redirect

    $response->content('
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Advanced Validation Form</title>
            <style>
                body { font-family: sans-serif; margin: 20px; background-color: #f8f8f8; }
                .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
                label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
                input[type="text"], input[type="email"], input[type="number"], input[type="password"], textarea, input[type="file"] {
                    width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
                }
                input[type="checkbox"] { margin-right: 8px; }
                button, input[type="submit"] {
                    background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;
                }
                button:hover, input[type="submit"]:hover { background-color: #0056b3; }
                .error-message { color: #dc3545; font-size: 0.9em; margin-top: -10px; margin-bottom: 10px; display: block; }
                h1, h2 { color: #333; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>User Registration</h1>
                ' . $notificationsHtml . '
                <form method="POST" action="/register" enctype="multipart/form-data">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="min 5, max 20, alphanumeric" value="' . htmlspecialchars($request->input('username', '')) . '">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['username'][0] ?? '') . '</small>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="valid email, unique (example)" value="' . htmlspecialchars($request->input('email', '')) . '">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['email'][0] ?? '') . '</small>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="min 8, with regex for strong password">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['password'][0] ?? '') . '</small>

                    <label for="password_confirmation">Confirm Password:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="must match password">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['password_confirmation'][0] ?? '') . '</small>

                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" placeholder="between 18 and 60, must be even" value="' . htmlspecialchars($request->input('age', '')) . '">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['age'][0] ?? '') . '</small>

                    <label for="homepage">Homepage URL (optional):</label>
                    <input type="text" id="homepage" name="homepage" placeholder="e.g. https://example.com" value="' . htmlspecialchars($request->input('homepage', '')) . '">
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['homepage'][0] ?? '') . '</small>

                    <label for="delivery_option">Delivery Option:</label><br>
                    <input type="radio" id="delivery_pickup" name="delivery_option" value="pickup" ' . ($request->input('delivery_option') === 'pickup' ? 'checked' : '') . '>
                    <label for="delivery_pickup" style="display: inline;">Pickup</label><br>
                    <input type="radio" id="delivery_ship" name="delivery_option" value="ship" ' . ($request->input('delivery_option') === 'ship' ? 'checked' : '') . '>
                    <label for="delivery_ship" style="display: inline;">Ship</label><br><br>

                    <label for="shipping_address">Shipping Address (required if "Ship" selected):</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" placeholder="Your shipping address if shipping">' . htmlspecialchars($request->input('shipping_address', '')) . '</textarea>
                    <small class="error-message">' . htmlspecialchars($request->query('errors')['shipping_address'][0] ?? '') . '</small>

                    <label for="avatar">Avatar (optional file upload):</label>
                    <input type="file" id="avatar" name="avatar"><br><br>

                    <input type="submit" value="Register">
                </form>

                <h2>Test JSON Submission</h2>
                <p>Use a tool like Postman or `curl` to send JSON to `/api/data`</p>
                <p>Example JSON Request Body (POST /api/data, Content-Type: application/json):</p>
                <pre><code>{
    "product_name": "Laptop Pro",
    "product_price": 1200.50,
    "tags": ["electronics", "tech"],
    "is_available": true
}</code></pre>
            </div>
        </body>
        </html>
    ');
    $response->send();

} elseif ($uriPath === '/register' && $request->method() === 'POST') {
    $data = $request->all(); // Combines POST and GET, but mainly POST here
    $fileData = $request->files();

    $validator = new Validator($data);
    $validator->setRules([
        'username' => 'bail|required|string|min:5|max:20|alpha_num',
        'email' => 'required|email|max:255', // Example: max length on email
        'password' => [
            'required',
            'min:8',
            //'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/' => 'Password must be at least 8 chars, incl. uppercase, lowercase, number, special char.'
        ],
        'password_confirmation' => 'required|same:password',
        'age' => 'required|int|between:18,60|even', // 'even' is our custom rule!
        'homepage' => 'url|max:255', 
        'delivery_option' => 'required|in:pickup,ship',
        'shipping_address' => 'required_if:delivery_option,ship|string|min:10', // Conditional validation
        'avatar' => function($field, $value, $data) use ($fileData) { // Custom callable rule for file (basic check)
            $file = $fileData[$field] ?? null;
            if ($file && $file['error'] === UPLOAD_ERR_NO_FILE) return true; // Optional file is fine
            if ($file && $file['error'] !== UPLOAD_ERR_OK) {
                return "File upload error: " . $file['error'];
            }
            if ($file && $file['size'] > 2 * 1024 * 1024) { // Max 2MB
                return "Avatar size must not exceed 2MB.";
            }
            // Basic mime type check for images
            if ($file && !in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                 return "Avatar must be a JPEG, PNG, or GIF image.";
            }
            return true; // File is OK or not uploaded
        }
    ]);

    // Custom messages
    $validator->setMessages([
        'username.required' => 'Please provide a username!',
        'username.min' => 'Username needs at least 5 characters.',
        'age.between' => 'You must be between 18 and 60 to register.',
    ]);

    if ($validator->validate()) {
        // Validation passed
        $validatedData = $validator->validatedData(); // Get clean, casted data

        // Example: Process data, save to DB, etc.
        // For files, move from tmp location: move_uploaded_file($fileData['avatar']['tmp_name'], 'uploads/' . $fileData['avatar']['name']);

        $response->addNotification("Registration successful! Welcome, {$validatedData['username']}.", 'success');
        $response->redirect('/success'); // Redirect to success page
    } else {
        // Validation failed
        $errors = $validator->errors()->get();
        // Redirect back to form with errors in query string (for simplicity, real apps use session flash)
        $queryErrors = http_build_query(['errors' => $errors]);
        $response->addNotification('Please correct the form errors.', 'error');
        // Pass original input back to form
        $queryInput = http_build_query($data);

        // Redirect with input and errors
        $redirectUrl = '/?' . $queryErrors . '&' . $queryInput;
        $response->redirect($redirectUrl, 302);
    }

} elseif ($uriPath === '/api/data' && $request->method() === 'POST') {
    // Example: API endpoint for JSON input validation
    $data = $request->json(); // Get data from JSON request body

    $validator = new Validator($data);
    $validator->setRules([
        'product_name' => 'required|string|min:3',
        'product_price' => 'required|between:0.01,9999.99',
        'tags' => 'array',
        'is_available' => 'boolean',
        'config_json' => 'json' // Expects a JSON string here
    ]);

    if ($validator->validate()) {
        $response->json([
            'status' => 'success',
            'message' => 'API data validated successfully.',
            'validated_data' => $validator->validatedData()
        ]);
    } else {
        $response->json([
            'status' => 'error',
            'message' => 'API data validation failed.',
            'errors' => $validator->errors()->get()
        ], 422);
    }
} elseif ($uriPath === '/success' && $request->method() === 'GET' ) {
    $notificationsHtml = displayMessages($response);
    $response->content('
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Success!</title>
            <style>
                body { font-family: sans-serif; margin: 20px; background-color: #f8f8f8; }
                .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; }
                .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                ' . $notificationsHtml . '
                <div class="success-icon">&#10004;</div>
                <h1>Success!</h1>
                <p>Your action was completed.</p>
                <p><a href="/">Go back to the form</a></p>
            </div>
        </body>
        </html>
    ')->send();
}

else {
    // 404 Not Found for any other path
    $response->status(404)->content('<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>')->send();
}