<?php

use Carbon\Carbon;
use FindBrok\WatsonBridge\Token;

/**
 * Class TestToken.
 */
class TestToken extends PHPUnit_Framework_TestCase
{
    /**
     * The token object.
     *
     * @var \FindBrok\WatsonBridge\Token
     */
    protected $token;

    /**
     * Setup test.
     */
    public function setUp()
    {
        $this->token = new Token('username');

        file_put_contents($this->getTokenStoragePath('token-username.json'), collect([
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ])->toJson(), LOCK_EX);
    }

    /**
     * Tear down test.
     */
    public function tearDown()
    {
        unset($this->token);

        unlink($this->getTokenStoragePath('token-username.json'));
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
     * Test that we can create the token object.
     *
     * @return void
     */
    public function testTokenObjectCanBeConstructed()
    {
        $token = new Token('username');
        $this->assertInstanceOf(Token::class, $token);
    }

    /**
     * Test that the method hasPayload works.
     *
     * @return void
     */
    public function testHasPayLoadMethod()
    {
        $token = new Token('username', [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ]);
        $this->assertTrue($token->hasPayLoad());

        $token2 = new Token('username2');
        $this->assertFalse($token2->hasPayLoad());
    }

    /**
     * Test that the method isExpired works.
     *
     * @return void
     */
    public function testIsExpiredAndIsNotExpiredMethod()
    {
        $token = new Token('username', [
            'token'      => 'sometoken',
            'expires_in' => '3600',
            'created'    => Carbon::now()->format('U'),
        ]);
        $this->assertTrue($token->isNotExpired());

        $token2 = new Token('username2', [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::createFromFormat('Y-m-d H:i:s', '2016-06-02 00:00:00')->format('U'),
        ]);
        $this->assertTrue($token2->isExpired());
    }

    /**
     * Test that the getFilePath method works.
     *
     * @return void
     */
    public function testGetFilePathMethod()
    {
        $this->assertEquals(
            realpath($this->getTokenStoragePath('token-username.json')),
            realpath($this->token->getFilePath())
        );
    }

    /**
     * Test that the exists method works.
     *
     * @return void
     */
    public function testExistsMethod()
    {
        $this->assertTrue($this->token->exists());
        $token2 = new Token('username2');
        $this->assertFalse($token2->exists());
    }

    /**
     * Test the save method to see if it works.
     *
     * @return void
     */
    public function testSaveMethod()
    {
        $payload = [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ];
        $token = new Token('username2', $payload);

        $this->assertTrue($token->save());
        $this->assertFileExists($this->getTokenStoragePath('token-username2.json'));
        $this->assertJsonStringEqualsJsonFile($this->getTokenStoragePath('token-username2.json'), collect($payload)->toJson());

        $this->deleteTestTokenFile('token-username2');
    }

    /**
     * Test that we can load a token from file and get its payload.
     *
     * @return void
     */
    public function testLoadFromFileMethodAndGetPayLoadMethod()
    {
        $this->createTestTokenFile('token-username3', [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => 1463977413,
        ]);

        $token = new Token('username3');
        $this->assertEquals([
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => 1463977413,
        ], $token->getPayload());

        $this->deleteTestTokenFile('token-username3');
    }

    /**
     * Test to see if the isValid method works.
     *
     * @return void
     */
    public function testIsValidMethod()
    {
        $this->assertTrue($this->token->isValid());
        $token2 = new Token('username2');
        $this->assertFalse($token2->isValid());

        $this->createTestTokenFile('token-username3', [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => 1463977413,
        ]);

        $token3 = new Token('username3');
        $this->assertFalse($token3->isValid());

        $this->deleteTestTokenFile('token-username3');
    }

    /**
     * Test the getToken method to see if it works.
     *
     * @return void
     */
    public function testGetTokenMethod()
    {
        $this->createTestTokenFile('token-username3', [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ]);

        $token3 = new Token('username3');
        $this->assertEquals('sometoken', $token3->getToken());

        $token2 = new Token('username2');
        $this->assertNull($token2->getToken());

        $this->deleteTestTokenFile('token-username3');
    }

    /**
     * Test to see if the Update token method works.
     *
     * @return void
     */
    public function testUpdateTokenMethod()
    {
        $payload = [
            'token'      => 'sometoken',
            'expires_in' => 3600,
            'created'    => Carbon::now()->format('U'),
        ];
        $token = new Token('username3', $payload);
        $this->assertEquals('sometoken', $token->getToken());

        $this->assertTrue($token->updateToken('newToken'));
        $this->assertEquals('newToken', $token->getToken());
        $this->assertFileExists($this->getTokenStoragePath('token-username3.json'));

        $this->deleteTestTokenFile('token-username3');
    }
}
