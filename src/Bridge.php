<?php

namespace FindBrok\WatsonBridge;

use FindBrok\WatsonBridge\Exceptions\WatsonBridgeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

class Bridge
{
    /**
     * Decide which method to use when sending request.
     *
     * @var string
     */
    protected $authMethod = 'credentials';

    /**
     * Guzzle http client for performing API request.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * API Endpoint for making request.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The limit for which we can re request token,
     * when performing request.
     *
     * @var int
     */
    protected $exceptionThrottle = 0;

    /**
     * Default headers.
     *
     * @var array
     */
    protected $headers = [
        'Accept'                    => 'application/json',
        'X-Watson-Learning-Opt-Out' => false,
    ];

    /**
     * API password.
     *
     * @var string
     */
    protected $password;

    /**
     * The WatsonToken.
     *
     * @var \FindBrok\WatsonBridge\Token
     */
    protected $token;

    /**
     * API Username.
     *
     * @var string
     */
    protected $username;

    /**
     * Create a new instance of bridge.
     *
     * @param string $username
     * @param string $password
     * @param string $endpoint
     */
    public function __construct($username, $password, $endpoint)
    {
        //Set Username, Password and Endpoint
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;

        //Set HttpClient
        $this->setClient($endpoint);

        //Set Token
        $this->token = new Token($this->username);
    }

    /**
     * Appends headers to the request.
     *
     * @param array $headers
     *
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
     * Clean options by removing empty items.
     *
     * @param array $options
     *
     * @return array
     */
    public function cleanOptions($options = [])
    {
        //If item is null or empty we will remove them
        return collect($options)->reject(function ($item) {
            return empty($item) || is_null($item);
        })->all();
    }

    /**
     * Clears throttle counter.
     *
     * @return void
     */
    public function clearThrottle()
    {
        $this->exceptionThrottle = 0;
    }

    /**
     * Make a DELETE Request to Watson.
     *
     * @param string $uri
     * @param        $data
     * @param string $type
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function delete($uri, $data, $type = 'json')
    {
        //Make a Delete and return response
        return $this->send('DELETE', $uri, $data, $type);
    }

    /**
     * Failed Request to Watson.
     *
     * @param Response $response
     *
     * @throws WatsonBridgeException
     */
    public function failedRequest(Response $response)
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
     * Fetch token from Watson and Save it locally.
     *
     * @param bool $incrementThrottle
     *
     * @return void
     */
    public function fetchToken($incrementThrottle = false)
    {
        //Increment throttle if needed
        if ($incrementThrottle) {
            $this->incrementThrottle();
        }
        //Reset Client
        $this->setClient($this->getAuthorizationEndpoint());
        //Get the token response
        $response = $this->get('v1/token', [
            'url' => $this->endpoint,
        ]);
        //Extract
        $token = json_decode($response->getBody()->getContents(), true);
        //Reset client
        $this->setClient($this->endpoint);
        //Update token
        $this->token->updateToken($token['token']);
    }

    /**
     * Make a GET Request to Watson.
     *
     * @param string $uri
     * @param array  $query
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function get($uri = '', $query = [])
    {
        //Make a Post and return response
        return $this->send('GET', $uri, $query, 'query');
    }

    /**
     * Return the authorization for making request.
     *
     * @return array
     */
    public function getAuth()
    {
        //Return access authorization
        return [$this->username, $this->password];
    }

    /**
     * Get the authorization endpoint for getting tokens.
     *
     * @return string
     */
    public function getAuthorizationEndpoint()
    {
        //Parse the endpoint
        $parsedEndpoint = collect(parse_url($this->endpoint));
        //Return auth url
        return $parsedEndpoint->get('scheme') . '://' . $parsedEndpoint->get('host') . '/authorization/api/';
    }

    /**
     * Return the Http client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        //Return client
        return $this->client;
    }

    /**
     * Return the headers used for making request.
     *
     * @return array
     */
    public function getHeaders()
    {
        //Return headers
        return $this->headers;
    }

    /**
     * Get Request options to pass along.
     *
     * @param array $initial
     *
     * @return array
     */
    public function getRequestOptions($initial = [])
    {
        //Define options
        $options = collect($initial);
        //Define an auth option
        if ($this->authMethod == 'credentials') {
            $options = $options->merge([
                'auth' => $this->getAuth(),
            ]);
        } elseif ($this->authMethod == 'token') {
            $this->appendHeaders([
                'X-Watson-Authorization-Token' => $this->getToken(),
            ]);
        }
        //Put Headers in options
        $options = $options->merge([
            'headers' => $this->getHeaders(),
        ]);
        //Clean and return
        return $this->cleanOptions($options->all());
    }

    /**
     * Get a token for authorization from Watson or Storage.
     *
     * @return string
     */
    public function getToken()
    {
        //Token is not valid
        if (! $this->token->isValid()) {
            //Fetch from Watson
            $this->fetchToken();
        }

        //Return token
        return $this->token->getToken();
    }

    /**
     * Increment throttle.
     *
     * @return void
     */
    public function incrementThrottle()
    {
        $this->exceptionThrottle++;
    }

    /**
     * Checks if throttle is reached.
     *
     * @return bool
     */
    public function isThrottledReached()
    {
        return $this->exceptionThrottle >= 2;
    }

    /**
     * Make a PATCH Request to Watson.
     *
     * @param string $uri
     * @param        $data
     * @param string $type
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function patch($uri, $data, $type = 'json')
    {
        //Make a Patch and return response
        return $this->send('PATCH', $uri, $data, $type);
    }

    /**
     * Make a POST Request to Watson.
     *
     * @param string $uri
     * @param mixed  $data
     * @param string $type
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function post($uri, $data, $type = 'json')
    {
        //Make a Post and return response
        return $this->send('POST', $uri, $data, $type);
    }

    /**
     * Make a PUT Request to Watson.
     *
     * @param string $uri
     * @param        $data
     * @param string $type
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function put($uri, $data, $type = 'json')
    {
        //Make a Put and return response
        return $this->send('PUT', $uri, $data, $type);
    }

    /**
     * Make a Request to Watson with credentials Auth.
     *
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request($method = 'GET', $uri = '', $options = [])
    {
        try {
            //Make the request
            return $this->getClient()->request($method, $uri, $this->getRequestOptions($options));
        } catch (ClientException $e) {
            //We are using token auth and probably token expired
            if ($this->authMethod == 'token' && $e->getCode() == 401 && ! $this->isThrottledReached()) {
                //Try refresh token
                $this->fetchToken(true);
                //Try requesting again
                return $this->request($method, $uri, $options);
            }
            //Clear throttle for this request
            $this->clearThrottle();
            //Call Failed Request
            $this->failedRequest($e->getResponse());
        }
    }

    /**
     * Send a Request to Watson.
     *
     * @param string $method
     * @param string $uri
     * @param mixed  $data
     * @param string $type
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function send($method, $uri, $data, $type = 'json')
    {
        //Make the Request to Watson
        $response = $this->request($method, $uri, [$type => $data]);
        //Request Failed
        if ($response->getStatusCode() != 200) {
            //Throw Watson Bridge Exception
            $this->failedRequest($response);
        }
        //We return response
        return $response;
    }

    /**
     * Creates the http client.
     *
     * @param string $endpoint
     *
     * @return void
     */
    public function setClient($endpoint = null)
    {
        //Create client using API endpoint
        $this->client = new Client([
            'base_uri' => ! is_null($endpoint) ? $endpoint : $this->endpoint,
        ]);
    }

    /**
     * Change the auth method.
     *
     * @param string $method
     *
     * @return self
     */
    public function useAuthMethodAs($method = 'credentials')
    {
        //Change auth method
        $this->authMethod = $method;
        //Return object
        return $this;
    }
}
