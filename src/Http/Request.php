<?php
namespace Classic\HttpTools\Http;

class Request
{
    private array $getParams;
    private array $postParams;
    private array $serverParams;
    private array $headers;
    private string $method;
    private string $uri;
    private string $body;
    private array $jsonBody = [];
    private array $files = [];

    /**
     * @param array|null $get Injected $_GET. If null, uses global $_GET.
     * @param array|null $post Injected $_POST. If null, uses global $_POST.
     * @param array|null $server Injected $_SERVER. If null, uses global $_SERVER.
     * @param string|null $body Injected raw request body. If null, uses file_get_contents('php://input').
     * @param array|null $files Injected $_FILES. If null, uses global $_FILES.
     */
    public function __construct(
        ?array $get = null,
        ?array $post = null,
        ?array $server = null,
        ?string $body = null,
        ?array $files = null
    ) {
        $this->getParams = $get ?? $_GET;
        $this->postParams = $post ?? $_POST;
        $this->serverParams = $server ?? $_SERVER;
        $this->files = $files ?? $_FILES;

        $this->method = $this->serverParams['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->serverParams['REQUEST_URI'] ?? '/';
        $this->body = $body ?? @file_get_contents('php://input'); // @ to suppress warnings in CLI when php://input is empty

        $this->headers = $this->parseHeaders();

        // Parse JSON body if content type is application/json
        if ($this->header('Content-Type') === 'application/json' && !empty($this->body)) {
            $this->jsonBody = json_decode($this->body, true) ?? [];
        }
    }

    /**
     * Parses HTTP headers from the injected serverParams.
     * Tries getallheaders() if available, otherwise reconstructs from $_SERVER.
     * @return array
     */
    private function parseHeaders(): array
    {
        // For testing, we'll ensure headers are passed via serverParams.
        // In a live web environment, getallheaders() or $_SERVER will populate this.
        $headers = [];

        // Prefer getallheaders() if it exists (for live web environments)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for CLI or other environments where getallheaders() is not available
            // This is primarily for headers explicitly provided as HTTP_NAME in serverParams
            foreach ($this->serverParams as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$headerName] = $value;
                }
            }
        }
        // Ensure Content-Type is captured if present in $_SERVER (common)
        if (isset($this->serverParams['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $this->serverParams['CONTENT_TYPE'];
        }
        return $headers;
    }


    /**
     * Get all GET parameters.
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->getParams;
    }

    /**
     * Get a specific GET parameter.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function query(string $key, $default = null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    /**
     * Get all POST parameters.
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->postParams;
    }

    /**
     * Get a specific POST parameter.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function post(string $key, $default = null): mixed
    {
        return $this->postParams[$key] ?? $default;
    }

    /**
     * Get parsed JSON body.
     * @param string|null $key Optional key to retrieve a specific part of the JSON.
     * @param mixed $default Default value if key is not found.
     * @return mixed
     */
    public function json(?string $key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->jsonBody;
        }
        return $this->jsonBody[$key] ?? $default;
    }

    /**
     * Get all input parameters (POST, GET, and JSON body combined, with precedence).
     * POST/JSON body overrides GET.
     * @return array
     */
    public function all(): array
    {
        // JSON body has highest precedence, then POST, then GET
        return array_merge($this->getParams, $this->postParams, $this->jsonBody);
    }

    /**
     * Get a specific input parameter (POST/JSON overrides GET).
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null): mixed
    {
        // Check JSON body first, then POST, then GET
        if (isset($this->jsonBody[$key])) {
            return $this->jsonBody[$key];
        }
        if (isset($this->postParams[$key])) {
            return $this->postParams[$key];
        }
        return $this->getParams[$key] ?? $default;
    }

    /**
     * Get all HTTP headers.
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, $default = null): mixed
    {
        // Headers are typically case-insensitive, so we normalize for lookup.
        $normalizedKey = strtolower($key);
        foreach ($this->headers as $headerName => $headerValue) {
            if (strtolower($headerName) === $normalizedKey) {
                return $headerValue;
            }
        }
        return $default;
    }

    /**
     * Get the HTTP method (e.g., 'GET', 'POST', 'PUT').
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get the request URI.
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get the raw request body.
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get uploaded file data for a specific field.
     * @param string $key The name of the file input field.
     * @return array|null File data array (name, type, tmp_name, error, size) or null if not found.
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all uploaded files.
     * @return array
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Check if the request is POST.
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Check if the request is GET.
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
}