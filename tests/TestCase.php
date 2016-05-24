<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class TestCase
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Watson bridge
     *
     * @var
     */
    protected $bridge;

    /**
     * Setup test
     */
    public function setUp()
    {
        $this->bridge = $this->getMockBuilder('FindBrok\WatsonBridge\Bridge')
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();
    }

    /**
     * Test a Successful Get request
     */
    public function testGetRequestResponseOk()
    {
        // Create a mock and queue one response
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->bridge->method('getClient')->willReturn($client);

        $this->assertEquals(200, $this->bridge->get('version/watson-api-method', ['foo' => 'bar'])->getStatusCode());
    }

    /**
     * Test a Get request which fails
     *
     * @expectedException \FindBrok\WatsonBridge\Exceptions\WatsonBridgeException
     */
    public function testGetRequestWithException()
    {
        // Create a mock and queue one response
        $mock = new MockHandler([
            new ClientException(
                'Watson Error',
                new Request('GET', 'version/watson-api-method'),
                new Response(400, ['X-Foo' => 'Bar'], collect(['error_code' => 400, 'error_message' => 'Watson Error'])->toJson())
            )
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->bridge->method('getClient')->willReturn($client);

        $this->bridge->get('version/watson-api-method', ['foo' => 'bar']);

        $this->setExpectedException('\FindBrok\WatsonBridge\Exceptions\WatsonBridgeException');
    }
}
