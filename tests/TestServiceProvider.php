<?php

use FindBrok\WatsonBridge\Bridge;
use Orchestra\Testbench\TestCase;
use FindBrok\WatsonBridge\WatsonBridgeServiceProvider;

class TestServiceProvider extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [WatsonBridgeServiceProvider::class];
    }

    /**
     * Test that Merging of Config is OK.
     */
    public function testConfigIsAccessible()
    {
        $this->assertEquals('SomeUsername', config('watson-bridge.credentials.default.username'));
        $this->assertEquals('SomePassword', config('watson-bridge.credentials.default.password'));
        $this->assertEquals('https://gateway.watsonplatform.net', config('watson-bridge.credentials.default.gateway'));
    }

    /**
     * Test that Service Bridge is Registered correctly.
     */
    public function testRegistrationOfServiceProvider()
    {
        /** @var Bridge $bridge */
        $bridge = $this->app->make(Bridge::class);
        $this->assertInstanceOf(Bridge::class, $bridge);
    }

    /**
     * Test that we can change service at will.
     */
    public function testUsingServiceMethodChangesTheClientCorrectly()
    {
        /** @var Bridge $bridge */
        $bridge = $this->app->make(Bridge::class);

        $bridge->usingService('personality_insights');
        $this->assertEquals('/personality-insights/api', $bridge->getClient()->getConfig('base_uri')->getPath());

        $bridge->usingService('tradeoff_analytics');
        $this->assertEquals('/tradeoff-analytics/api', $bridge->getClient()->getConfig('base_uri')->getPath());
    }

}
