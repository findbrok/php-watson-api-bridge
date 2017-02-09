<?php

namespace FindBrok\WatsonBridge;

use Illuminate\Support\ServiceProvider;
use FindBrok\WatsonBridge\Exceptions\WatsonBridgeException;

class WatsonBridgeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish Config.
        $this->publishes([
                             __DIR__.'/config/watson-bridge.php' => config_path('watson-bridge.php'),
                         ], 'watson-api-bridge');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // Merge configs.
        $this->mergeConfigFrom(__DIR__.'/config/watson-bridge.php', 'watson-bridge');

        // Bind Bridge in the IOC.
        $this->app->bind(Bridge::class, function ($app, array $args = ['use' => 'default']) {
            return $this->constructBridge($args);
        });
    }

    /**
     * Construct Watson Bridge.
     *
     * @param array $args
     *
     * @throws WatsonBridgeException
     * @return Bridge
     */
    protected function constructBridge(array $args)
    {
        // A credential name is necessary.
        if (! isset($args['use'])) {
            throw new WatsonBridgeException('Could not construct Bridge, please specify a credential name.', 500);
        }

        // Get credentials array.
        $credentials = $this->getCredentials($args['use']);

        // Make sure credentials information is available.
        if (! isset($credentials['username']) || ! isset($credentials['password']) || ! isset($credentials['gateway'])) {
            throw new WatsonBridgeException('Could not construct Bridge, missing some information in credentials.',
                                            500);
        }

        // Make bridge.
        $bridge = new Bridge($credentials['username'], $credentials['password'], $credentials['gateway']);
        $bridge->appendHeaders(['X-Watson-Learning-Opt-Out' => config('watson-bridge.x_watson_learning_opt_out')]);

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
