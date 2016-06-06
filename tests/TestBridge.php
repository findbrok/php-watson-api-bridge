<?php

use Carbon\Carbon;
use FindBrok\WatsonBridge\Bridge;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class TestBridge.
 */
class TestBridge extends PHPUnit_Framework_TestCase
{
    /**
     * Watson bridge.
     *
     * @var
     */
    protected $bridge;

    /**
     * Setup test.
     */
    public function setUp()
    {
        $this->bridge = $this->getMockBuilder('FindBrok\WatsonBridge\Bridge')
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();

        $this->createTestTokenFile('token-foo', [
            'token'      => 'someToken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ]);

        $reflected = new ReflectionClass(Bridge::class);
        $constructor = $reflected->getConstructor();
        $constructor->invoke($this->bridge, 'foo', 'password', 'url');
    }

    /**
     * TearDown Test.
     */
    public function tearDown()
    {
        unset($this->bridge);
        $this->deleteTestTokenFile('token-foo');
    }

    /**
     * Return Token Storage Folder.
     *
     * @param string $file
     *
     * @return string
     */
    public function getTokenStoragePath($file = '')
    {
        return __DIR__.'/../src/Storage/'.$file;
    }

    /**
     * Get response body for a token.
     *
     * @return string
     */
    public function getTokenResponseBody()
    {
        return file_get_contents(__DIR__.'/fixtures/raw-token.json');
    }

    /**
     * Creates a test token file.
     *
     * @param string $name
     * @param array  $data
     *
     * @return void
     */
    public function createTestTokenFile($name = '', $data = [])
    {
        file_put_contents(
            $this->getTokenStoragePath($name.'.json'),
            collect($data)->toJson(),
            LOCK_EX
        );
    }

    /**
     * Delete a test token file.
     *
     * @param string $name
     *
     * @return void
     */
    public function deleteTestTokenFile($name = '')
    {
        unlink($this->getTokenStoragePath($name.'.json'));
    }

    /**
     * Test that we are able to create bridge object.
     *
     * @return voids
     */
    public function testBridgeObjectCanBeConstructed()
    {
        $bridge = new Bridge('username', 'password', 'endpoint');
        $this->assertInstanceOf(Bridge::class, $bridge);
    }

    /**
     * Test that we get the correct Auth endpoint for getting token.
     *
     * @return void
     */
    public function testGetAuthEndpointMethodCorrectEndpointReturned()
    {
        $bridge = new Bridge('username', 'password', 'https://gateway.watsonplatform.net/service/api/');
        $this->assertEquals('https://gateway.watsonplatform.net/authorization/api/', $bridge->getAuthorizationEndpoint());

        $bridge2 = new Bridge('username', 'password', 'https://stream.watsonplatform.net/service/api/');
        $this->assertEquals('https://stream.watsonplatform.net/authorization/api/', $bridge2->getAuthorizationEndpoint());
    }

    /**
     * Test that we can set and reset the client correctly.
     *
     * @return void
     */
    public function testSetClientMethodClientCorrectlySet()
    {
        $bridge = new Bridge('username', 'password', 'https://gateway.watsonplatform.net/service/api/');
        $this->assertInstanceOf(Client::class, $bridge->getClient());

        $client = new Client([
            'base_uri'  => 'https://gateway.watsonplatform.net/service/api/',
        ]);
        $this->assertEquals($client->getConfig('base_uri'), $bridge->getClient()->getConfig('base_uri'));

        $bridge->setClient($bridge->getAuthorizationEndpoint());
        $this->assertNotEquals($client->getConfig('base_uri'), $bridge->getClient()->getConfig('base_uri'));
    }

    /**
     * Test that the getRequestOptions method works correctly.
     *
     * @return void
     */
    public function testGetRequestOptionsMethodWillReturnCorrectOptions()
    {
        // Create a mock and queue one response
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $this->getTokenResponseBody()),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->bridge->method('getClient')->willReturn($client);

        $data = [
            'foo' => 'bar',
        ];

        $this->assertEquals([
            'json'    => ['foo' => 'bar'],
            'auth'    => ['foo', 'password'],
            'headers' => [
                'Accept'                    => 'application/json',
                'X-Watson-Learning-Opt-Out' => false,
            ],
        ], $this->bridge->useAuthMethodAs('credentials')->getRequestOptions(['json' => $data]));

        $this->assertEquals([
            'json'    => ['foo' => 'bar'],
            'headers' => [
                'Accept'                       => 'application/json',
                'X-Watson-Learning-Opt-Out'    => false,
                'X-Watson-Authorization-Token' => 'someToken',
            ],
        ], $this->bridge->useAuthMethodAs('token')->getRequestOptions(['json' => $data]));
    }

    /**
     * Test a Successful Get request.
     *
     * @return void
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
     * Test that the getToken method works.
     *
     * @return void
     */
    public function testGetTokenMethodWhenTokenNotValidAndFetchedFromWatsonWithOkToken()
    {
        // Create a mock and queue one response
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $this->getTokenResponseBody()),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->bridge->method('getClient')->willReturn($client);

        $reflected = new ReflectionClass(Bridge::class);
        $constructor = $reflected->getConstructor();
        $constructor->invoke($this->bridge, 'username', 'password', 'url');

        $this->assertEquals('someToken', $this->bridge->getToken());
    }

    /**
     * Test that the getToken method works when token is saved.
     *
     * @return void
     */
    public function testGetTokenMethodWhenTokenIsAlreadyInCache()
    {
        $this->assertEquals('someToken', $this->bridge->getToken());
    }

    /**
     * Test the getToken method with an expired token, we fetch the token from
     * Watson again.
     *
     * @return void
     */
    public function testGetTokenMethodWhenTokenExpiredAndFetchTokenAgain()
    {
        $this->createTestTokenFile('token-foofoo', [
            'token'      => 'oldToken',
            'expires_in' => 3600,
            'created'    => 1463977413,
        ]);

        // Create a mock and queue one response
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $this->getTokenResponseBody()),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->bridge->method('getClient')->willReturn($client);

        $reflected = new ReflectionClass(Bridge::class);
        $constructor = $reflected->getConstructor();
        $constructor->invoke($this->bridge, 'foofoo', 'password', 'url');

        $this->assertEquals('someToken', $this->bridge->getToken());

        $this->deleteTestTokenFile('token-foofoo');
    }

    /**
     * Test a Get request which fails.
     *
     * @expectedException \FindBrok\WatsonBridge\Exceptions\WatsonBridgeException
     }*/

    /**
     * Test that when the token is expired we refresh the token and try again.
     *
     * @return void
     */
    public function testTokenExpiredWhenMakingRequestWeRefreshTokenAndTryAgain()
    {
        $this->createTestTokenFile('token-foobar', [
            'token'      => 'oldToken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ]);

        $expectedResponseBody = collect(['someData' => 'data'])->toJson();

        // Create a mock and queue responses
        $mock = new MockHandler([
            new ClientException(
                'Watson Error',
                new Request('GET', 'version/watson-api-method'),
                new Response(401, ['X-Foo' => 'Bar'], collect(['error_code' => 401, 'error_message' => 'unauthorized access'])->toJson())
            ),
            new Response(200, ['X-Foo' => 'Bar'], $this->getTokenResponseBody()),
            new Response(200, ['X-Foo' => 'Bar'], $expectedResponseBody),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->bridge->method('getClient')->willReturn($client);

        $reflected = new ReflectionClass(Bridge::class);
        $constructor = $reflected->getConstructor();
        $constructor->invoke($this->bridge, 'foobar', 'password', 'url');

        $this->bridge->useAuthMethodAs('token');
        $this->assertEquals('oldToken', $this->bridge->getToken());

        $this->assertEquals(
            $expectedResponseBody,
            $this->bridge->useAuthMethodAs('token')->get('version/watson-api-method')->getBody()->getContents()
        );

        $this->deleteTestTokenFile('token-foobar');
    }
}
