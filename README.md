# PHP IBM Watson API Bridge

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4e21093a-cc60-4a75-b7fe-cb29053faf6c/big.png)](https://insight.sensiolabs.com/projects/4e21093a-cc60-4a75-b7fe-cb29053faf6c)

[![StyleCI](https://styleci.io/repos/59507474/shield?style=flat)](https://styleci.io/repos/59507474)
[![Build Status](https://travis-ci.org/findbrok/php-watson-api-bridge.svg?branch=master)](https://travis-ci.org/findbrok/php-watson-api-bridge)
[![Latest Stable Version](https://poser.pugx.org/findbrok/php-watson-api-bridge/v/stable)](https://packagist.org/packages/findbrok/php-watson-api-bridge)
[![Total Downloads](https://poser.pugx.org/findbrok/php-watson-api-bridge/downloads)](https://packagist.org/packages/findbrok/php-watson-api-bridge)
[![Latest Unstable Version](https://poser.pugx.org/findbrok/php-watson-api-bridge/v/unstable)](https://packagist.org/packages/findbrok/php-watson-api-bridge)
[![License](https://poser.pugx.org/findbrok/php-watson-api-bridge/license)](https://packagist.org/packages/findbrok/php-watson-api-bridge)

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

// Create a new bridge Object.
$bridge = new Bridge('username', 'password', 'baseUrl');

// Simple get request.
$queryParams = ['foo' => 'bar'];
$response = $bridge->get('uri', $queryParams);

// Simple post request.
$dataToPost = ['foo' => 'bar'];
$response = $bridge->post('uri', $dataToPost, 'json');
```

The Package uses [Guzzle](http://docs.guzzlephp.org/en/latest/testing.html) to perform requests, 
all your responses will be instances of ```GuzzleHttp\Psr7\Response```

---
### Integration with Laravel 5

As of version 1.1.x, PHP Watson API bridge adds a new Service Provider which integrates easily with Laravel 5.

First add the ServiceProvider to your ```app.php``` file.

```php
'providers' => [
   ....
   FindBrok\WatsonBridge\WatsonBridgeServiceProvider::class,
]
```

Now publish the config file.

```php
$ php artisan vendor:publish --tag=watson-api-bridge
```

You will now have a config file ```watson-bridge.php``` in your config directory. 
You may define in this config file your credentials, auth method to use,
Watson Services and so on.

### Services

The Laravel Integration gives you 3 service classes that are bound to the IoC.
- Bridge (The actual Bridge class for making requests to Watson)
- Carpenter (Which can construct Bridge instances using your credentials and service URL)
- BridgeStack (Essentially a store where you can keep all Bridges you constructed and retrieve them back.)

## Bridge

Bridge class will help you make requests to Watson API using the ```get```, ```post```, ```put```, ```patch``` methods
