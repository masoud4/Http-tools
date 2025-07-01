<?php
namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use masoud4\HttpTools\Http\Response;

class ResponseTest extends TestCase
{
    private static array $capturedHeaders = [];
    private static int $capturedHttpStatusCode = 0;
    private static string $capturedContent = '';

    /** @var \PHPUnit\Framework\MockObject\MockObject|Response */
    private $responseMock;

    protected function setUp(): void
    {
        // Reset captured state before each test method
        self::$capturedHeaders = [];
        self::$capturedHttpStatusCode = 0;
        self::$capturedContent = '';

        // Create a mock for the Response class, only mocking `doSend()`.
        $this->responseMock = $this->getMockBuilder(Response::class)
                                   ->onlyMethods(['doSend'])
                                   ->getMock();

        // Configure the mocked `doSend` method to capture the state.
        $this->responseMock->method('doSend')
                           ->willReturnCallback(function() {
                               self::$capturedHttpStatusCode = $this->responseMock->getHttpStatusCode();
                               self::$capturedHeaders = $this->responseMock->getHeaders();
                               self::$capturedContent = $this->responseMock->getContent();
                               // No exit() call here.
                           });
    }

    public function testConstructor(): void
    {
        $response = new Response('Hello', 201, ['X-Custom' => 'Value']);
        $this->assertEquals('Hello', $response->getContent());
        $this->assertEquals(201, $response->getHttpStatusCode());
        $this->assertEquals(['Content-Type' => 'text/html; charset=UTF-8', 'X-Custom' => 'Value'], $response->getHeaders());
    }

    public function testContent(): void
    {
        $response = new Response();
        $response->content('New Content');
        $this->assertEquals('New Content', $response->getContent());
    }

    public function testStatus(): void
    {
        $response = new Response();
        $response->status(404);
        $this->assertEquals(404, $response->getHttpStatusCode());
    }

    public function testHeader(): void
    {
        $response = new Response();
        $response->header('X-App-Version', '1.0');
        $this->assertEquals(['Content-Type' => 'text/html; charset=UTF-8', 'X-App-Version' => '1.0'], $response->getHeaders());
    }

    public function testHeaders(): void
    {
        $response = new Response();
        $response->headers(['X-Framework' => 'HttpTools', 'Cache-Control' => 'no-cache']);
        $expectedHeaders = [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Framework' => 'HttpTools',
            'Cache-Control' => 'no-cache',
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());
    }

    public function testAddNotification(): void
    {
        $response = new Response();
        $response->addNotification('Item created.', 'success');
        $response->addNotification('Invalid input.', 'error');

        $expectedNotifications = [
            ['type' => 'success', 'message' => 'Item created.'],
            ['type' => 'error', 'message' => 'Invalid input.'],
        ];
        $this->assertEquals($expectedNotifications, $response->getNotifications());
    }

    // --- Tests for public methods that internally call `doSend()` ---

    public function testSendMethodOutputsContentAndSetsHeaders(): void
    {
        // Set content and headers on the mock
        $this->responseMock->content('Test Output')->status(200)->header('X-Test', 'Value');
        
        // --- FIX: Call the public send() method directly, removing the JSON hack ---
        $this->responseMock->send(); 

        // Assertions against the captured static properties
        $this->assertEquals(200, self::$capturedHttpStatusCode);
        $this->assertEquals('Value', self::$capturedHeaders['X-Test']);
        $this->assertEquals('text/html; charset=UTF-8', self::$capturedHeaders['Content-Type']); // This should now pass
        $this->assertEquals('Test Output', self::$capturedContent);
    }

    public function testJsonMethodOutputsJsonAndSetsHeaders(): void
    {
        $data = ['status' => 'success', 'message' => 'API OK'];
        $this->responseMock->json($data, 200);

        $this->assertEquals(200, self::$capturedHttpStatusCode);
        $this->assertEquals('application/json', self::$capturedHeaders['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode($data), self::$capturedContent);
    }

    public function testJsonMethodWithNotificationsOutputsJsonAndSetsHeaders(): void
    {
        $this->responseMock->addNotification('Data saved!', 'success');
        $data = ['status' => 'success'];

        $expectedData = $data;
        $expectedData['notifications'] = [['type' => 'success', 'message' => 'Data saved!']];

        $this->responseMock->json($data, 200);

        $this->assertEquals(200, self::$capturedHttpStatusCode);
        $this->assertEquals('application/json', self::$capturedHeaders['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedData), self::$capturedContent);
    }

    public function testRedirectMethodSetsLocationHeader(): void
    {
        $url = '/dashboard';
        $this->responseMock->redirect($url, 302);

        $this->assertEquals(302, self::$capturedHttpStatusCode);
        $this->assertEquals($url, self::$capturedHeaders['Location']);
        $this->assertEquals('', self::$capturedContent);
    }

    public function testRedirectMethodWithNotificationsInQueryString(): void
    {
        $this->responseMock->addNotification('Redirect Success', 'info');
        $url = '/target';

        $this->responseMock->redirect($url, 302);

        $expectedNotifications = [['type' => 'info', 'message' => 'Redirect Success']];
        $expectedQuery = http_build_query(['_notifications' => json_encode($expectedNotifications)]);
        $expectedLocation = '/target?' . $expectedQuery;

        $this->assertEquals(302, self::$capturedHttpStatusCode);
        $this->assertEquals($expectedLocation, self::$capturedHeaders['Location']);
        $this->assertEquals('', self::$capturedContent);
    }
}