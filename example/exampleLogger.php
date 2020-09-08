<?php

require __DIR__."/../vendor/autoload.php";

use Schema31\PhpMonitoring\Logger;
use Schema31\PhpMonitoring\LoggerConstants;

define('LOGGER_STREAMNAME', 'streamName');
define('LOGGER_AUTHENTICATION', 'authentication');
define('LOGGER_THRESHOLD', LoggerConstants::DEBUG);

$logger = new Logger();

/*$logger->setProtocol(LoggerConstants::REST)
->setLevel(LoggerConstants::ALERT)
->setFacility("PhpMonitoring")
->setFile(__FILE__)
->setLine(__LINE__)
->setShortMessage("Short Message " . uniqid())
->setFullMessage("Full Message")
->setSingleAdditional("key1", "value1")
->publish();

$logger->setProtocol(LoggerConstants::REST)
//->setLevel(LoggerConstants::ALERT)
->setFacility("PhpMonitoring")
->setFile(__FILE__)
->setLine(__LINE__)
->setShortMessage("Short Message " . uniqid())
->setFullMessage("Full Message")
->setSingleAdditional("key1", "value1")
->publish();*/

try{
    throw new \Exception("Test Exception");
}catch(\Exception $exc){
    $logger
    ->setException($exc)
    ->publish();
}