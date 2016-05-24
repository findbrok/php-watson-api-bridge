# PHP IBM Watson API Bridge

[![Build Status](https://travis-ci.org/findbrok/php-watson-api-bridge.svg?branch=master)](https://travis-ci.org/findbrok/php-watson-api-bridge)

A simple PHP wrapper for IBM Watson API

### Installation

```
composer require findbrok/php-watson-api-bridge
```

### Usage

Before using the package checkout [Watson API Explorer](https://watson-api-explorer.mybluemix.net/),
to get a sense of what you can and cannot do with Watson

```php
require 'vendor/autoload.php'

use FindBrok\WatsonBridge\Bridge;

//Create a new bridge Object
$bridge = new Bridge('username', 'password', 'baseUrl');

//Simple get request
$queryParams = ['foo' => 'bar'];
$response = $bridge->get('uri', $queryParams);

//Simple post request
$dataToPost = ['foo' => 'bar'];
$response = $bridge->post('uri', $dataToPost, 'json');
```

The Package uses [Guzzle](http://docs.guzzlephp.org/en/latest/testing.html) to perform requests, 
all your responses will be instances of ```GuzzleHttp\Psr7\Response```

