![banner](.github/assets/banner.png?raw=true)

# NetSapiens REST API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/simtabi/netsapiens.svg?style=flat-square)](https://packagist.org/packages/simtabi/netsapiens)
[![Total Downloads](https://img.shields.io/packagist/dt/simtabi/netsapiens.svg?style=flat-square)](https://packagist.org/packages/simtabi/netsapiens)
![GitHub Actions](https://github.com/simtabi/netsapiens/actions/workflows/main.yml/badge.svg)

A PHP REST API client for calling the NetSapiens API (http://netsapiens.com).

API specific documentation can be found online at the [ns-api API Reference
](https://api.netsapiens.com/ns-api/webroot/apidoc/) website.

## Features
1. REST API client
2. Command line client — coming soon


## Installation

You can install the package via composer:

```bash
composer require simtabi/netsapiens
```

### Usage

```php
use Simtabi\NetSapiens\NetSapiens;
use Simtabi\NetSapiens\Helpers\Helpers;

// API auth credentials
$clientId         = ''; // your client id
$clientSecret     = ''; // your client secret
$username         = ''; // your username
$password         = ''; // your password
$baseUrl          = ''; // i.e https://api.netsapiens.com/

$guzzleConfig     = []; // Guzzle HTTP client configuration

// Path to store cache files
$cachePath        = ''; // path to where the cache would be stored

// Initialize the API client
try {
    return NetSapiens::getInstance(
        clientId     : $clientId,
        clientSecret : $clientSecret,
        username     : $username,
        password     : $password,
        baseUrl      : $baseUrl,
        cacher       : function (array $args) use ($cachePath) {
                        $cacheData = $args[0];
                        $cacheId   = $args[1];
                        
                        return Helpers::cacher(
                            items :            $cacheData,
                            cacheId:           $cacheId,
                            cachePath :        $cachePath,
                            lifeTimeInSeconds: Helpers::CACHE_LIFETIME_IN_SECONDS,
                            resetCache:        true,
                        );
    },
        guzzleConfig : $guzzleConfig
    );
} catch (NetSapiensException $exception) {
    // do something if we encountered errors
    echo $exception->getMessage();
}

// you can access resource specific error by calling the following method

```

Accessing the ``Authentication`` object instance
```php
$oAuth2 = $netSapiensClient->getOAuth2();
```

Accessing all errors encountered during an ``OAuth2`` call
```php
$errors = $netSapiensClient->getOAuth2()->getErrors();
```

Using the helper method
```php
$netSapiensClient = netSapiensClient($clientId, $clientSecret, $username, $password, $baseUrl, $cacher, $guzzleConfig);

// returns a NetSapiens instance or a string if there were errors found
```

### N.B
If you do not want to use the default caching mechanism.
``cacher`` accepts a ``callback function`` where you can pass in a custom caching implementation.

# Available Resources
Resources marked in green are ready for use.

| #   | Resource        |  Ready?  |
|:----|:----------------|:---------|
| 1   | Address         | ❌        |
| 2   | Agent           | ❌        |
| 3   | AgentLog        | ❌        |
| 4   | AnswerRule      | ❌        |
| 5   | Audio           | ❌        |
| 6   | Call | ❌        |
| 7   | CallCenterStats | ❌        |
| 8   | CallerIDEmergency | ❌        |
| 9   | CallQueue | ✅        |
| 10  | CallQueueEmailReport | ❌        |
| 11  | CallQueueStats | ❌        |
| 12  | CallRequest | ❌        |
| 13  | CDR2 | ❌        |
| 14  | CDRExport | ❌        |
| 15  | CDRSchedule | ❌        |
| 16  | Chart | ❌        |
| 17  | Conference | ❌        |
| 18  | ConferenceParticipant | ❌        |
| 19  | ConferenceRecord | ❌        |
| 20  | Connection | ❌        |
| 21  | Contact | ❌        |
| 22  | Dashboard | ❌        |
| 23  | Default | ❌        |
| 24  | Department | ❌        |
| 25  | Device | ❌        |
| 26  | DeviceModel | ❌        |
| 27  | DeviceProfile | ❌        |
| 28  | DialPlan | ❌        |
| 29  | DialPolicy | ❌        |
| 30  | DialRule | ❌        |
| 31  | Domain | ❌        |
| 32  | Image | ❌        |
| 33  | MAC | ❌        |
| 34  | Meeting | ❌        |
| 35  | Message | ❌        |
| 36  | MessageSession | ❌        |
| 37  | NDPServer | ❌        |
| 38  | Permission | ❌        |
| 39  | PhoneConfiguration | ❌        |
| 40  | PhoneNumber | ❌        |
| 41  | Presence | ❌        |
| 42  | Queued | ❌        |
| 43  | Quota | ❌        |
| 44  | Recording | ❌        |
| 45  | Reseller | ❌        |
| 46  | Route | ❌        |
| 47  | ServerInfo | ❌        |
| 48  | SFU | ❌        |
| 49  | Site | ❌        |
| 50  | Sites | ❌        |
| 51  | SMSNumber | ❌        |
| 52  | Subscriber | ❌        |
| 53  | Subscription | ❌        |
| 54  | TimeFrame | ❌        |
| 55  | TimeRange | ❌        |
| 56  | Trace | ❌        |
| 57  | Turn | ❌        |
| 58  | UCInbox | ❌        |
| 59  | UIConfig | ❌        |
| 60  | Upload | ❌        |
| 61  | VoicemailReminders | ❌        |

## 1. Create a Call Queue

```php

// Call queue details
$phoneNumber = '';
$queue       = '';
$uid         = '';

// Create a call queue
$newCallQueue = $netSapiensClient
                ->getCallQueue()
                ->setQueue($queue)
                ->setUid($uid)
                ->create($phoneNumber);
```

Only use ``$netSapiensClient instanceof NetSapiens`` when using the helper function. i.e
```php
if($netSapiensClient instanceof NetSapiens) {
    $newCallQueue = $netSapiensClient
                    ->getCallQueue()
                    ->setQueId($queue)
                    ->setUid($uid)
                    ->create($phoneNumber);
    
    if ($newCallQueue) {
        // do something if successful
    }
} else {
    // do something if we have errors
}
```

### Accessing the ``Request`` object instance
This can only be achieved after making a resource request. For example, you can access the ``Request`` instance after creating a call queue.


```php
$callQueue  = $netSapiensClient->getCallQueue()->setQueue($queue)->setUid($uid);
$callNumber = $callQueue->create($phoneNumber);
$errors     = $callQueue->getRequest()->getErrors();
```
If you do not want to use the default caching mechanism.
``cacher`` accepts a ``callback function`` where you can pass in a custom caching implementation.


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email io@io.com instead of using the issue tracker.

## Credits

-   [Imani Manyara](https://github.com/imanimanyara)
-   [Easter Mukora](https://github.com/eastermukora)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
