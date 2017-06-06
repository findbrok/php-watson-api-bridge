<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Credentials to use
    |--------------------------------------------------------------------------
    |
    | Here you may define the default service credentials to use
    | for performing API calls to Watson.
    |
    */

    'default_credentials' => env('WATSON_API_DEFAULT_CREDENTIALS', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may define credentials for your Watson Service
    | you should find them in your Bluemix console. You can define as
    | many credentials as you want
    |
    */

    'credentials' => [
        'default' => [
            'username' => env('WATSON_API_USERNAME', 'SomeUsername'),
            'password' => env('WATSON_API_PASSWORD', 'SomePassword'),
            'gateway'  => env('WATSON_API_GATEWAY', 'https://gateway.watsonplatform.net'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | These are Watson's Services Url, you may define as many as you want.
    |
    */

    'services' => [
        'alchemy'                     => '/alchemy-api/calls',
        'conversation'                => '/conversation/api',
        'document_conversion'         => '/document-conversion/api',
        'language_translator'         => '/language-translator/api',
        'natural_language_classifier' => '/natural-language-classifier/api',
        'personality_insights'        => '/personality-insights/api',
        'retrieve_and_rank'           => '/retrieve-and-rank/api',
        'tone_analyzer'               => '/tone-analyzer/api',
        'speech_to_text'              => '/speech-to-text/api',
        'text_to_speech'              => '/text-to-speech/api',
        'visual_recognition'          => '/visual-recognition/api',
        'alchemydata_News'            => '/alchemy-api/calls',
        'discovery'                   => '/discovery/api',
        'tradeoff_analytics'          => '/tradeoff-analytics/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Auth Method
    |--------------------------------------------------------------------------
    |
    | The Default Auth Method we must use to authenticate with Watson.
    |
    */

    'default_auth_method' => 'credentials',

    /*
    |--------------------------------------------------------------------------
    | Auth Methods
    |--------------------------------------------------------------------------
    |
    | These are the available auth methods. Default is credentials.
    |
    */

    'auth_methods' => [
        'credentials',
        'token',
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Watson-Learning-Opt-Out
    |--------------------------------------------------------------------------
    |
    | By default, Watson collects data from all requests and uses the data
    | to improve the service. If you do not want to share your data,
    | set this value to true.
    |
    */

    'x_watson_learning_opt_out' => false,
];
