<?php

namespace App\Cron;

use App\library\requestMonitor;

class cronJob {
    

    public function __construct () {

    }
    /**
     * All function already available , combine it here
     * Neeeded function : fetch all ip stats 
     * process: 
     */
    public function update_user_monitor_stats() {
        $requestMonitor =  new requestMonitor(NULL);
        $requestMonitor->fetchAllIPStats();

    }
}
// $cron_job = new cron_job;
// $cron_job->update_user_monitor_stats();
?>