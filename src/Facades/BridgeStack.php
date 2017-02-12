<?php

namespace FindBrok\WatsonBridge\Facades;

use Illuminate\Support\Facades\Facade;
use FindBrok\WatsonBridge\Support\BridgeStack as Concrete;

class BridgeStack extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return Concrete::class;
    }
}
