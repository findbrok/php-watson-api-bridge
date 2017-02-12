<?php

namespace FindBrok\WatsonBridge\Facades;

use Illuminate\Support\Facades\Facade;
use FindBrok\WatsonBridge\Bridge as Concrete;

class Bridge extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return Concrete::class;
    }
}
