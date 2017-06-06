<?php

namespace FindBrok\WatsonBridge;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;
use FindBrok\WatsonBridge\Support\Carpenter;
use FindBrok\WatsonBridge\Support\BridgeStack;
use Illuminate\Contracts\Foundation\Application;

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
            __DIR__.'/../config/watson-bridge.php' => config_path('watson-bridge.php'),
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
        $this->mergeConfigFrom(__DIR__.'/../config/watson-bridge.php', 'watson-bridge');

        // The Carpenter must be an Instance because we need only one.
        $this->app->singleton(Carpenter::class, function () {
            return new Carpenter();
        });

        // The Bridge Stack also must be the same across the entire app.
        $this->app->singleton(BridgeStack::class, function (Application $app) {
            return new BridgeStack($app->make(Carpenter::class), []);
        });

        // Registers a Default Bridge.
        $this->registerDefaultBridge();
    }

    /**
     * Registers the Default Bridge.
     */
    protected function registerDefaultBridge()
    {
        $this->app->bind(Bridge::class, function (Application $app) {
            /** @var Carpenter $carpenter */
            $carpenter = $app->make(Carpenter::class);
            /** @var Repository $config */
            $config = $app->make(Repository::class);

            return $carpenter->constructBridge(
                $config->get('watson-bridge.default_credentials'),
                null,
                $config->get('watson-bridge.default_auth_method')
            );
        });
    }
}
