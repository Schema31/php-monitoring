<?php

namespace Schema31\PhpMonitoring;

use Schema31\GCloudMonitoringSDK\gCloud_Monitoring;

class Logger {

    private $streamName = '';
    private $authentication = '';
    private $threshold = LoggerConstants::DEBUG;

    private $protocol = LoggerConstants::REST;
    private $level = null;
    private $facility = null;
    private $file = null;
    private $line = null;
    private $shortMessage = null;
    private $fullMessage = null;
    private $additionals = [];
    private $sapiName = null;

    private $gCloudMonitoring;

    public function __construct(
        string $streamName = '', 
        string $authentication = '', 
        string $protocol = LoggerConstants::REST, 
        int $threshold = LoggerConstants::DEBUG)
    {
        $this->streamName = defined("LOGGER_STREAMNAME") ? LOGGER_STREAMNAME : $streamName;
        $this->authentication = defined("LOGGER_AUTHENTICATION") ? LOGGER_AUTHENTICATION : $authentication;
        $this->threshold = defined("LOGGER_THRESHOLD") ? LOGGER_THRESHOLD : $threshold;
        if($this->threshold < -1 || $this->threshold > LoggerConstants::DEBUG){
            throw new \Exception("The threshold must be between -1 and " . LoggerConstants::DEBUG);
        }

        $this->setProtocol(defined("LOGGER_PROTOCOL") ? LOGGER_PROTOCOL : $protocol);
    }

    public function setProtocol(string $protocol = LoggerConstants::REST){
        if(!in_array($protocol, [LoggerConstants::REST, LoggerConstants::GELF])){
            throw new \Exception("Protocol $protocol not supported");
        }
        $this->protocol = $protocol;

        return $this;
    }

    public function setLevel(int $level = LoggerConstants::DEBUG){
        if($level < LoggerConstants::EMERGENCY || $level > LoggerConstants::DEBUG){
            throw new \Exception("The level must be between " . LoggerConstants::EMERGENCY . " and " . LoggerConstants::DEBUG);
        }
        $this->level = $level;

        return $this;
    }

    public function setFacility(string $facility){
        if(trim($facility) == ''){
            throw new \Exception("Facility not valid");
        }
        $this->facility = $facility;

        return $this;
    }

    public function setFile(string $file){
        if(trim($file) == ''){
            throw new \Exception("File not valid");
        }
        $this->file = $file;

        return $this;
    }

    public function setLine(string $line){
        if(trim($line) == ''){
            throw new \Exception("Line not valid");
        }
        $this->line = $line;

        return $this;
    }

    public function setShortMessage(string $shortMessage){
        if(trim($shortMessage) == ''){
            throw new \Exception("ShortMessage not valid");
        }
        $this->shortMessage = $shortMessage;

        return $this;
    }

    public function setFullMessage(string $fullMessage){
        if(trim($fullMessage) == ''){
            throw new \Exception("FullMessage not valid");
        }

        if (is_string($this->shortMessage) && strlen($this->shortMessage) > 255) {
            $fullMessage = $this->shortMessage . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . $fullMessage;
        }
        $this->fullMessage = $fullMessage;

        return $this;
    }

    public function setSingleAdditional(string $name, $value){
        $this->additionals[$name] = $value;

        return $this;
    }

    public function setMultipleAttitionals($additionals){
        if (is_object($additionals)) {
            $additionals = get_object_vars($additionals);
        }
        if (is_array($additionals)) {
            foreach ($additionals as $key => $value) {
                $this->setSingleAdditional($key, $value);
            }
        }

        return $this;
    }

    public function setException(\Exception $exc, int $level = LoggerConstants::ERROR){
        $this->setLevel($level);
        $this->setFacility(get_class($exc));
        $this->setFile($exc->getFile());
        $this->setLine($exc->getLine());
        $this->setShortMessage("#" . $exc->getCode()." ".get_class($exc).": ".$exc->getMessage());
        $this->setFullMessage($exc->getTraceAsString());

        return $this;
    }

    public function publish(){
		if(is_integer($this->threshold)){
			if($this->threshold < $this->level){
				return $this;
			}
        }

        $this->gCloudMonitoring = new gCloud_Monitoring($this->streamName, $this->authentication);
        $this->gCloudMonitoring->protocol = $this->protocol;
        if(!is_null($this->level)){
            $this->gCloudMonitoring->message->setLevel($this->level);
        }
        if(!is_null($this->facility)){
            $this->gCloudMonitoring->message->setFacility($this->facility);
        }
        if(!is_null($this->file)){
            $this->gCloudMonitoring->message->setFile($this->file);
        }
        if(!is_null($this->line)){
            $this->gCloudMonitoring->message->setLine($this->line);
        }
        if(!is_null($this->shortMessage)){
            $this->gCloudMonitoring->message->setShortMessage($this->shortMessage);
        }
        if(!is_null($this->fullMessage)){
            $this->gCloudMonitoring->message->setFullMessage($this->fullMessage);
        }
        $this->setSapiName();
        $this->setCurrentUrl();
        $this->setMemoryPeakUsage();
        foreach ($this->additionals as $key => $value) {
            $v = $value;
            if(is_array($v) || is_object($v)){
                $v = serialize($v);
            }
            $this->gCloudMonitoring->message->setAdditional($key, $v);
        }

        $this->gCloudMonitoring->publish();

        $this->clear();

        return $this;
    }

    private function setSapiName(){
        $this->sapiName = php_sapi_name();
        $this->setSingleAdditional("requestMethod", $this->sapiName);

        return $this;
    }

    private function setCurrentUrl(){
        if(isset($_SERVER) && array_key_exists("HTTP_HOST", $_SERVER) && array_key_exists("REQUEST_URI", $_SERVER)){
            //Figure out whether we are using http or https.
            $http = 'http';
            //If HTTPS is present in our $_SERVER array, the URL should
            //start with https:// instead of http://
            if(isset($server['HTTPS'])){
                $http = 'https';
            }
            //Get the HTTP_HOST.
            $host = $_SERVER['HTTP_HOST'];
            //Get the REQUEST_URI. i.e. The Uniform Resource Identifier.
            $requestUri = $_SERVER['REQUEST_URI'];
            //Finally, construct the full URL.
            //Use the function htmlentities to prevent XSS attacks.
            $completeUrl = $http . '://' . htmlentities($host) . '/' . htmlentities($requestUri);

            $this->setSingleAdditional("currentUrl", $completeUrl);
        }

        return $this;
    }

    private function setMemoryPeakUsage(){
        $this->setSingleAdditional("memoryGetPeakUsage", memory_get_peak_usage(TRUE));

        return $this;
    }

    private function clear(){
        $this->level = null;
        $this->facility = null;
        $this->file = null;
        $this->line = null;
        $this->shortMessage = null;
        $this->fullMessage = null;
        $this->additionals = [];
        $this->sapiName = null;
    }
}