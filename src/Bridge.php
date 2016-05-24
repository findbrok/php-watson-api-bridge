<?php

namespace FindBrok\WatsonBridge;

use FindBrok\WatsonBridge\Exceptions\WatsonBridgeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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
     * Default headers
     *
     * @var array
     */
    protected $headers = [
        'Accept' => 'application/json',
        'X-Watson-Learning-Opt-Out' => false
    ];

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
        return [$this->username, $this->password];
    }

    /**
     * Appends headers to the request
     *
     * @param array $headers
     * @return self
     */
    public function appendHeaders($headers = [])
    {
        //We have some headers to append
        if (! empty($headers)) {
            //Append headers
            $this->headers = collect($this->headers)->merge($headers)->all();
        }
        //Return calling object
        return $this;
    }

    /**
     * Return the headers used for making request
     *
     * @return array
     */
    public function getHeaders()
    {
        //Return headers
        return  $this->headers;
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

    /**
     * Clean options by removing empty items
     *
     * @param array $options
     * @return array
     */
    public function cleanOptions($options = [])
    {
        //If item is null or empty we will remove them
        return collect($options)->reject(function ($item) {
            return (empty($item) || is_null($item));
        })->all();
    }

    /**
     * Failed Request to Watson
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @throws WatsonBridgeException
     */
    public function failedRequest($response)
    {
        //Decode Response
        $decodedResponse = json_decode($response->getBody()->getContents(), true);
        //Get error message
        $errorMessage = (isset($decodedResponse['error_message']) && ! is_null($decodedResponse['error_message'])) ?
            $decodedResponse['error_message'] :
            $response->getReasonPhrase();
        //ClientException
        throw new WatsonBridgeException($errorMessage, $response->getStatusCode());
    }

    /**
     * Make a Request to Watson
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request($method = 'GET', $uri = '', $options = [])
    {
        try {
            //Make the request
            return $this->getClient()->request($method, $uri, $options);
        } catch (ClientException $e) {
            //Call Failed Request
            $this->failedRequest($e->getResponse());
        }
    }

    /**
     * Send a Request to Watson
     *
     * @param string $method
     * @param string $uri
     * @param mixed $data
     * @param string $type
     * @return \GuzzleHttp\Psr7\Response
     */
    private function send($method = 'POST', $uri, $data, $type = 'json')
    {
        //Make a Post Request
        $response = $this->request($method, $uri, $this->cleanOptions([
            'headers' => $this->getHeaders(),
            'auth' => $this->getAuth(),
            $type => $data
        ]));
        //Request Failed
        if ($response->getStatusCode() != 200) {
            //Throw Watson Bridge Exception
            $this->failedRequest($response);
        }
        //We return response
        return $response;
    }

    /**
     * Make a GET Request to Watson
     *
     * @param string $uri
     * @param array $query
     * @return \GuzzleHttp\Psr7\Response
     */
    public function get($uri = '', $query = [])
    {
        //Make a Post and return response
        return $this->send('GET', $uri, $query, 'query');
    }

    /**
     * Make a POST Request to Watson
     *
     * @param string $uri
     * @param mixed $data
     * @param string $type
     * @return \GuzzleHttp\Psr7\Response
     */
    public function post($uri = '', $data, $type = 'json')
    {
        //Make a Post and return response
        return $this->send('POST', $uri, $data, $type);
    }

    /**
     * Make a PUT Request to Watson
     *
     * @param string $uri
     * @param $data
     * @param string $type
     * @return \GuzzleHttp\Psr7\Response
     */
    public function put($uri = '', $data, $type = 'json')
    {
        //Make a Put and return response
        return $this->send('PUT', $uri, $data, $type);
    }

    /**
     * Make a PATCH Request to Watson
     *
     * @param string $uri
     * @param $data
     * @param string $type
     * @return \GuzzleHttp\Psr7\Response
     */
    public function patch($uri = '', $data, $type = 'json')
    {
        //Make a Patch and return response
        return $this->send('PATCH', $uri, $data, $type);
    }

    /**
     * Make a DELETE Request to Watson
     *
     * @param string $uri
     * @param $data
     * @param string $type
     * @return \GuzzleHttp\Psr7\Response
     */
    public function delete($uri = '', $data, $type = 'json')
    {
        //Make a Delete and return response
        return $this->send('DELETE', $uri, $data, $type);
    }
}
