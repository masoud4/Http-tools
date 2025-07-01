<?php
namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Http\Request;

/**
 * @runInSeparateProcess
 * Ensures each test runs in a fresh PHP process to isolate global state.
 */
class RequestTest extends TestCase
{
    // Helper to simulate a request for testing.
    // It now purely prepares the data to be injected into the Request constructor.
    protected function simulateRequest(
        string $method = 'GET',
        string $uri = '/',
        array $get = [],
        array $post = [],
        string $body = '',
        array $headers = [], // This will directly influence the 'serverParams' array
        array $files = []
    ): Request {
        $serverParams = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
        ];

        // Populate $_SERVER-like array with HTTP_ headers for Request's parseHeaders method
        foreach ($headers as $key => $value) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $serverParams[$serverKey] = $value;
        }
        // Special case for Content-Type, which might also be available as CONTENT_TYPE in $_SERVER
        if (isset($headers['Content-Type'])) {
            $serverParams['CONTENT_TYPE'] = $headers['Content-Type'];
        }

        // Instantiate Request, injecting mock data.
        return new Request($get, $post, $serverParams, $body, $files);
    }

    // No tearDown needed as each test runs in a separate process.

    public function testGetQueryParams(): void
    {
        $request = $this->simulateRequest('GET', '/?', ['param1' => 'value1', 'param2' => 'value2']);
        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2'], $request->getQueryParams());
    }

    public function testQueryMethod(): void
    {
        $request = $this->simulateRequest('GET', '/?', ['param1' => 'value1']);
        $this->assertEquals('value1', $request->query('param1'));
        $this->assertNull($request->query('non_existent'));
        $this->assertEquals('default', $request->query('non_existent', 'default'));
    }

    public function testGetPostParams(): void
    {
        $request = $this->simulateRequest('POST', '/', [], ['field1' => 'post_value']);
        $this->assertEquals(['field1' => 'post_value'], $request->getPostParams());
    }

    public function testPostMethod(): void
    {
        $request = $this->simulateRequest('POST', '/', [], ['field1' => 'post_value']);
        $this->assertEquals('post_value', $request->post('field1'));
        $this->assertNull($request->post('non_existent'));
        $this->assertEquals('default', $request->post('non_existent', 'default'));
    }

    public function testAllMethodCombinesGetPostJson(): void
    {
        $request = $this->simulateRequest(
            'POST',
            '/?query=get_val',
            ['query' => 'get_val', 'common' => 'get_val_get'],
            ['post' => 'post_val', 'common' => 'post_val_post'],
            '{"json_key": "json_val", "common": "json_val_json"}',
            ['Content-Type' => 'application/json']
        );

        $expected = [
            'query' => 'get_val',
            'common' => 'json_val_json', // JSON overrides POST, POST overrides GET
            'post' => 'post_val',
            'json_key' => 'json_val',
        ];

        // Ensure order of merge is consistent and correctly represents precedence.
        // `Request::all()` merges GET, then POST, then JSON.
        $actual = $request->all();
        $this->assertEquals($expected, $actual);
    }

    public function testInputMethodPrecedence(): void
    {
        $request = $this->simulateRequest(
            'POST',
            '/?q=get_val&p=get_val&j=get_val',
            ['q' => 'get_val', 'p' => 'get_val', 'j' => 'get_val'],
            ['p' => 'post_val', 'j' => 'post_val'],
            '{"j": "json_val"}',
            ['Content-Type' => 'application/json']
        );

        $this->assertEquals('json_val', $request->input('j'));    // JSON takes precedence
        $this->assertEquals('post_val', $request->input('p'));    // POST takes precedence over GET
        $this->assertEquals('get_val', $request->input('q'));     // Only in GET
        $this->assertNull($request->input('non_existent'));
        $this->assertEquals('default', $request->input('non_existent', 'default'));
    }

    public function testJsonMethod(): void
    {
        $request = $this->simulateRequest(
            'POST',
            '/',
            [], [],
            '{"name": "Test", "age": 30}',
            ['Content-Type' => 'application/json']
        );
        $this->assertEquals(['name' => 'Test', 'age' => 30], $request->json());
        $this->assertEquals('Test', $request->json('name'));
        $this->assertNull($request->json('non_existent'));
        $this->assertEquals('default', $request->json('non_existent', 'default'));

        // Test with invalid JSON
        $request = $this->simulateRequest(
            'POST',
            '/',
            [], [],
            '{"name": "Test", "age": 30', // Malformed JSON
            ['Content-Type' => 'application/json']
        );
        $this->assertEmpty($request->json());
    }

    public function testHeaders(): void
    {
        $request = $this->simulateRequest(
            'GET', '/', [], [], '',
            ['User-Agent' => 'PHPUnit/1.0', 'Accept' => 'application/json', 'X-Custom-Header' => 'CustomValue']
        );
        // Request's parseHeaders will derive these from serverParams
        $this->assertEquals('PHPUnit/1.0', $request->header('User-Agent'));
        $this->assertEquals('application/json', $request->header('Accept'));
        $this->assertEquals('CustomValue', $request->header('X-Custom-Header'));
        $this->assertEquals('PHPUnit/1.0', $request->header('user-agent')); // Test case-insensitivity

        $this->assertNull($request->header('Non-Existent'));

        $headers = $request->headers();
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertCount(3, $headers); // Ensure no unexpected headers
    }


    public function testMethod(): void
    {
        $request = $this->simulateRequest('GET');
        $this->assertEquals('GET', $request->method());
        $this->assertTrue($request->isGet());
        $this->assertFalse($request->isPost());

        $request = $this->simulateRequest('POST');
        $this->assertEquals('POST', $request->method());
        $this->assertTrue($request->isPost());
        $this->assertFalse($request->isGet());
    }

    public function testUri(): void
    {
        $request = $this->simulateRequest('GET', '/test/path?q=1');
        $this->assertEquals('/test/path?q=1', $request->uri());
    }

    public function testGetBody(): void
    {
        $request = $this->simulateRequest('POST', '/', [], [], 'raw request body content');
        $this->assertEquals('raw request body content', $request->getBody());
    }

    public function testFiles(): void
    {
        $testFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpUploader',
            'error' => UPLOAD_ERR_OK,
            'size' => 123
        ];
        $request = $this->simulateRequest('POST', '/', [], [], '', [], ['my_file' => $testFile]);

        $this->assertEquals(['my_file' => $testFile], $request->files());
        $this->assertEquals($testFile, $request->file('my_file'));
        $this->assertNull($request->file('non_existent_file'));
    }
}