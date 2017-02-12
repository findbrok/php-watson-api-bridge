<?php

namespace FindBrok\WatsonBridge\Facades;

use Illuminate\Support\Facades\Facade;
use FindBrok\WatsonBridge\Support\Carpenter as Concrete;

class Carpenter extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return Concrete::class;
    }
}
