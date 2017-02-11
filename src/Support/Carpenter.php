<?php

namespace FindBrok\WatsonBridge\Support;

use FindBrok\WatsonBridge\Bridge;
use FindBrok\WatsonBridge\Exceptions\WatsonBridgeException;

class Carpenter
{
    /**
     * Constructs a new Bridge.
     *
     * @param string $credential
     * @param string $service
     * @param string $authMethod
     *
     * @throws WatsonBridgeException
     * @return Bridge
     */
    public function constructBridge($credential, $service = null, $authMethod = 'credentials')
    {
        // Get credentials array.
        $credentials = $this->getCredentials($credential);

        // Make sure credentials information is available.
        if (! isset($credentials['username']) || ! isset($credentials['password']) || ! isset($credentials['gateway'])) {
            throw new WatsonBridgeException('Could not construct Bridge, missing some information in credentials.',
                                            500);
        }

        // Make bridge.
        $bridge = $this->makeRawBridge()
                       ->setUsername($credentials['username'])
                       ->setPassword($credentials['password'])
                       ->setEndPoint($credentials['gateway'])
                       ->setClient($credentials['gateway'])
                       ->appendHeaders(['X-Watson-Learning-Opt-Out' => config('watson-bridge.x_watson_learning_opt_out')]);

        // Add service.
        $bridge = $this->addServiceToBridge($bridge, $service);

        // Choose an auth method.
        $bridge = $this->chooseAuthMethodForBridge($bridge, $authMethod);

        return $bridge;
    }

    /**
     * Creates and return a raw bridge.
     *
     * @return Bridge
     */
    protected function makeRawBridge()
    {
        return new Bridge();
    }

    /**
     * Adds a Service to the Bridge.
     *
     * @param Bridge $bridge
     * @param string $service
     *
     * @return Bridge
     */
    protected function addServiceToBridge(Bridge $bridge, $service = null)
    {
        // Add a service if necessary.
        if (! is_null($service)) {
            $bridge->usingService($service);
        }

        return $bridge;
    }

    /**
     * Choose an Auth Method for the Bridge.
     *
     * @param Bridge $bridge
     * @param string $username
     * @param string $authMethod
     *
     * @return Bridge
     */
    protected function chooseAuthMethodForBridge(Bridge $bridge, $username, $authMethod = null)
    {
        // Check if an auth method is passed explicitly.
        if (! is_null($authMethod) && collect(config('watson-bridge.auth_methods'))->contains($authMethod)) {
            $bridge->useAuthMethodAs($authMethod);

            // Auth method is token so we need to set Token.
            if ($authMethod == 'token') {
                $bridge->setToken($username);
            }
        }

        return $bridge;
    }

    /**
     * Get credentials to use.
     *
     * @param string $name
     *
     * @throws WatsonBridgeException
     * @return array
     */
    protected function getCredentials($name = 'default')
    {
        // We must make sure that credentials exists.
        if (! config()->has('watson-bridge.credentials.'.$name)) {
            throw new WatsonBridgeException('Credentials "'.$name.'" does not exist, try specifying it in the watson-bridge config.',
                                            500);
        }

        return config('watson-bridge.credentials.'.$name);
    }
}
