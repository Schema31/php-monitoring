<?php

namespace Schema31\PhpMonitoring;

class LoggerConstants {
    
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    const REST = 'REST';
    const GELF = 'GELF';
}