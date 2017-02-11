<?php

use FindBrok\WatsonBridge\Bridge;
use Orchestra\Testbench\TestCase;
use FindBrok\WatsonBridge\Support\Carpenter;
use FindBrok\WatsonBridge\Support\BridgeStack;
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
        $bridge = $this->app->make(Bridge::class);
        $this->assertInstanceOf(Bridge::class, $bridge);

        $carpenter = $this->app->make(Carpenter::class);
        $this->assertInstanceOf(Carpenter::class, $carpenter);

        $bridgeStack = $this->app->make(BridgeStack::class);
        $this->assertInstanceOf(BridgeStack::class, $bridgeStack);
    }

    /**
     * Test that we can create bridges correctly.
     */
    public function testCarpenterCreateBridgesCorrectly()
    {
        /** @var Carpenter $carpenter */
        $carpenter = $this->app->make(Carpenter::class);
        $bridge = $carpenter->constructBridge('default', 'personality_insights');

        $this->assertEquals('/personality-insights/api', $bridge->getClient()->getConfig('base_uri')->getPath());

        $bridge2 = $carpenter->constructBridge('default', 'tradeoff_analytics');
        $this->assertEquals('/tradeoff-analytics/api', $bridge2->getClient()->getConfig('base_uri')->getPath());
    }

    /**
     * Tests that the Default Bridge is being constructed.
     */
    public function testWeCanConstructADefaultBridge()
    {
        /** @var Bridge $bridge */
        $bridge = $this->app->make(Bridge::class);
        $this->assertInstanceOf(Bridge::class, $bridge);

        $this->assertEquals(['SomeUsername', 'SomePassword'], $bridge->getAuth());
    }
}
