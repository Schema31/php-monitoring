# php-monitoring
======

[Packagist][link-packagist]

Logger class for PHP. It require the [Schema31/php-gcloud-monitoring-sdk](https://github.com/Schema31/php-gcloud-monitoring-sdk)

Install
-------

You can install the library using [composer](https://getcomposer.org/):

```sh
$ composer require schema31/php-monitoring
```

How to use
----------

## Configurations

### Internal configuration

You can pass the configuration values:

```php
use Schema31\PhpMonitoring\Logger;
use Schema31\PhpMonitoring\LoggerConstants;

...

$logger = new Logger("streamName", "authentication", LoggerConstants::REST, LoggerConstants::DEBUG);

```

### Configuration constants

You can define constants and the library use it automatically: 

```php
use Schema31\PhpMonitoring\Logger;
use Schema31\PhpMonitoring\LoggerConstants;

...

define('LOGGER_STREAMNAME', 'streamName');
define('LOGGER_AUTHENTICATION', 'authentication');
define('LOGGER_PROTOCOL', LoggerConstants::REST);
define('LOGGER_THRESHOLD', LoggerConstants::DEBUG);

$logger = new Logger();
```

## Sending a message

```php
//Method chaining is allowed
$logger->setProtocol(LoggerConstants::REST) //if you want to ovverride the default
->setLevel(LoggerConstants::ALERT)
->setFacility("PhpMonitoring")
->setFile(__FILE__)
->setLine(__LINE__)
->setShortMessage("Short Message " . uniqid())
->setFullMessage("Full Message")
->setSingleAdditional("key1", "value1")
->publish();
```

## Sending an exception

You can send a log based on an exception:

```php
try{
    throw new \Exception("Test Exception");
}catch(\Exception $exc){
    $logger
    ->setException($exc)
    ->publish();
}
```

Or

```php
try{
    throw new \Exception("Test Exception");
}catch(\Exception $exc){
    $logger
    ->setException($exc)
    ->setFacility("PhpMonitoring") //You can override the facility of the exception
    ->setLevel(LoggerConstants::CRITICAL) //You can override the level of the exception
    ->setSingleAdditional("key1", "value1") //You can add other additionals
    ->publish();
}
```

[link-packagist]: https://packagist.org/packages/schema31/php-monitoring
