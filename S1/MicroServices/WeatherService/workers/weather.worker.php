<?php

namespace Workers;

require_once __DIR__."/../library/weather.lib.php";
require_once __DIR__."/../library/log.lib.php";
require_once __DIR__."/../utils/cache.php";
require_once __DIR__."/../utils/timeManager.php";

use \utils\timeManager;
use function \library\logg;

class Weather_worker {
    /**
     * 
     * Log the start time while contructing or worker starting
     */
    public function __construct() {
        logg(file:"backend_log",message:"The Worker Started At : ".timeManager::utcNow());
    }
    public function work () {

    }
    public function run () {
    
    }
    
    /**
     * 
     * Log the end time while the worker ending
     */
    public function __destruct() {
        logg(file:"backend_log",message:"The Worker Started At : ".timeManager::utcNow());
    }
}

?>