<p align="center">
	<img src="https://raw.githubusercontent.com/findbrok/art-work/master/packages/php-watson-api-bridge/php-watson-api-bridge.png">
</p>
<h2 align="center">
	PHP IBM Watson API Bridge
</h2>

<p align="center">
    <a href="https://packagist.org/packages/findbrok/php-watson-api-bridge"><img src="https://poser.pugx.org/findbrok/php-watson-api-bridge/v/stable" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/findbrok/php-watson-api-bridge"><img src="https://poser.pugx.org/findbrok/php-watson-api-bridge/v/unstable" alt="Latest Unstable Version"></a>
    <a href="https://travis-ci.org/findbrok/php-watson-api-bridge"><img src="https://travis-ci.org/findbrok/php-watson-api-bridge.svg?branch=1.1" alt="Build Status"></a>
    <a href="https://styleci.io/repos/59507474"><img src="https://styleci.io/repos/59507474/shield?branch=1.1" alt="StyleCI"></a>
    <a href="https://packagist.org/packages/findbrok/php-watson-api-bridge"><img src="https://poser.pugx.org/findbrok/php-watson-api-bridge/license" alt="License"></a>
    <a href="https://packagist.org/packages/findbrok/php-watson-api-bridge"><img src="https://poser.pugx.org/findbrok/php-watson-api-bridge/downloads" alt="Total Downloads"></a>
    <a href="https://insight.sensiolabs.com/projects/4e21093a-cc60-4a75-b7fe-cb29053faf6c" alt="medal"><img src="https://insight.sensiolabs.com/projects/4e21093a-cc60-4a75-b7fe-cb29053faf6c/mini.png"></a>
</p>

## Introduction
PHP IBM Watson API Bridge, provides a simple and easy to use wrapper around the IBM Watson API. The library makes it easier for us to
develop PHP apps that use the IBM Watson API.

## License
PHP IBM Watson API Bridge is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Installation
```bash
$ composer require findbrok/php-watson-api-bridge
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

> If you are using Laravel >= 5.5, you can skip service registration 
> and aliases registration thanks to Laravel auto package discovery 
> feature.

First add the ServiceProvider to your ```app.php``` file:

```php
'providers' => [
   ....
   FindBrok\WatsonBridge\WatsonBridgeServiceProvider::class,
]
```

You can also add the following aliases to you ```app.php``` file:

```php
'aliases' => [
    ...
    'Bridge'      => FindBrok\WatsonBridge\Facades\Bridge::class,
    'BridgeStack' => FindBrok\WatsonBridge\Facades\BridgeStack::class,
    'Carpenter'   => FindBrok\WatsonBridge\Facades\Carpenter::class,
]
```

Now publish the config file:

```bash
$ php artisan vendor:publish --tag=watson-api-bridge
```

You will now have a config file ```watson-bridge.php``` in your config directory. 
You may define in this config file your credentials, auth method to use, Watson Services and so on.

### Services
The Laravel Integration gives you 3 service classes that are bound to the IoC.
- FindBrok\WatsonBridge\Bridge (The actual Bridge class for making requests to Watson)
- FindBrok\WatsonBridge\Support\Carpenter (Which can construct Bridge instances using your credentials and service URL)
- FindBrok\WatsonBridge\Support\BridgeStack (Essentially a store where you can keep all Bridges you constructed and retrieve them back.)

### Bridge
```Bridge``` class will help you make requests to Watson API using the ```get```, ```post```, ```put```, ```patch``` methods:

```php
$response = $bridge->get('uri', $queryParams);
```

### Carpenter
The ```Carpenter``` class can build any type of Bridge for you. Use the ```constructBridge``` method passing in the desired parameters like
credentials name, service to use and auth method and so on and the ```Carpenter``` will.

```php
$carpenter = app()->make(Carpenter::class);

$bridge = $carpenter->constructBridge('default', 'personality_insights');
```

Remember that your credentials names, services and auth methods are all defined in the ```watson-bridge.php``` config file. 

### BridgeStack
The ```BridgeStack``` is a great place to keep all your Bridges so that you can retrieve them anytime in your app.
Use the ```mountBridge``` method to construct and keep any type of Bridge in the Stack.

```php
$stack = app()->make(BridgeStack::class);

$stack->mountBridge('myPIBridge', 'default', 'personality_insights');
$stack->mountBridge('myTABridge', 'default', 'tradeoff_analytics');

// Now use the Bridges stored in the Stack.
$response = $stack->conjure('myPIBridge')->post('/v3/profile', $dataToPost);
```
The ```BridgeStack``` is essentially a Laravel Collection, thus you have access to all Collection methods.
 
### Facades
If you are using Laravel version less than 5.4 you have access to 3 Facades for the 3 services Bridge, Carpenter and BridgeStack.
Since Laravel 5.4 added automatic Facades you won't be needing those classes.
 
- FindBrok\WatsonBridge\Facades\Bridge
- FindBrok\WatsonBridge\Facades\BridgeStack
- FindBrok\WatsonBridge\Facades\Carpenter
 
Remember that if you are resolving the Bridge directly from the IoC and not constructing it with the Carpenter class a default Bridge will
be resolved for you using the default credentials and auth methods from your watson-bridge config. 
 
### Credits
Big Thanks to all developers who worked hard to create something amazing!
 
### Creator
[![Percy Mamedy](https://img.shields.io/badge/Author-Percy%20Mamedy-orange.svg)](https://twitter.com/PercyMamedy)

Twitter: [@PercyMamedy](https://twitter.com/PercyMamedy)
<br/>
GitHub: [percymamedy](https://github.com/percymamedy)
