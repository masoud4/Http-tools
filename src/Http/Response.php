<?php
namespace Classic\HttpTools\Http;

class Response
{
    private string $content;
    private int $statusCode;
    private array $headers;
    private array $notifications = [];

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8',
        ], $headers);
    }

    /**
     * Set the HTTP status code.
     * @param int $statusCode
     * @return self
     */
    public function status(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Set the response content.
     * @param string $content
     * @return self
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set a response header.
     * @param string $key
     * @param string $value
     * @return self
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set multiple response headers.
     * @param array $headers
     * @return self
     */
    public function headers(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Add a notification message to the response.
     * @param string $message The notification message.
     * @param string $type The type of notification (e.g., 'success', 'error', 'warning', 'info').
     * @return self
     */
    public function addNotification(string $message, string $type = 'info'): self
    {
        $this->notifications[] = ['type' => $type, 'message' => $message];
        return $this;
    }

    /**
     * Get all notifications for this response.
     * @return array
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * Send a JSON response.
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->header('Content-Type', 'application/json');
        // Include notifications in JSON response
        if (!empty($this->notifications)) {
            $data['notifications'] = $this->notifications;
        }
        $this->content(json_encode($data));
        $this->status($statusCode);
        $this->doSend(); // Call the protected method
    }

    /**
     * Redirect to a new URL.
     * @param string $url The URL to redirect to.
     * @param int $statusCode The HTTP status code for redirection (e.g., 302, 301).
     * @param array $notifications Optional: notifications to pass via query string for simple cases
     * @return void
     */
    public function redirect(string $url, int $statusCode = 302, array $notifications = []): void
    {
        // Merge current notifications with any new ones for redirect
        $allNotifications = array_merge($this->notifications, $notifications);

        if (!empty($allNotifications)) {
            $query = http_build_query(['_notifications' => json_encode($allNotifications)]);
            $url .= (str_contains($url, '?') ? '&' : '?') . $query;
        }

        $this->status($statusCode);
        $this->header('Location', $url);
        $this->doSend(); // Call the protected method
    }

    /**
     * The actual send logic, separated for testability.
     * In a real web environment, this will send headers, output content, and exit.
     * In tests, this method can be overridden by a mock.
     * @return void
     */
    protected function doSend(): void
    {
        // Check if headers have already been sent to prevent errors
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $key => $value) {
                header("{$key}: {$value}");
            }
        }
        echo $this->content;
        exit; // Terminate script execution
    }
    /**
     * Send the HTTP response to the client.
     * This is the public entry point that triggers the actual sending.
     * @return void
     */
    public function send(): void
    {
        $this->doSend(); // Delegate to the protected method for testability
    }

  
    public function getHttpStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}