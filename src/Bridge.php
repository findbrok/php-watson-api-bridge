<?php

namespace FindBrok\WatsonBridge;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class Bridge
 *
 * @package FindBrok\WatsonBridge
 */
class Bridge
{
    /**
     * API Username
     *
     * @var string
     */
    protected $username;

    /**
     * API password
     *
     * @var string
     */
    protected $password;

    /**
     * API Endpoint for making request
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Guzzle http client for performing API request
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The request object
     *
     * @var \GuzzleHttp\Psr7\Request
     */
    protected $request;

    /**
     * Create a new instance of bridge
     *
     * @param string $username
     * @param string $password
     * @param string $endpoint
     */
    public function __construct($username = null, $password = null, $endpoint = null)
    {
        //Set Username, Password and Endpoint
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
        //Set HttpClient
        $this->setClient();
    }

    /**
     * Return the authorization for making request
     *
     * @return array
     */
    public function getAuth()
    {
        //Return access authorization
        return [
            'auth' => [$this->username, $this->password]
        ];
    }

    /**
     * Creates the http client
     *
     * @return void
     */
    private function setClient()
    {
        //Create client using API endpoint
        $this->client = new Client([
            'base_uri'  => $this->endpoint,
        ]);
    }

    /**
     * Return the Http client instance
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        //Return client
        return $this->client;
    }
}
