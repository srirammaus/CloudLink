<?php

namespace App\Console\Commands;


use App\Cron\cronJob;
use Illuminate\Console\Command;

class Cron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //not in user
    protected $signature = 'cronjob:update_user_monitor_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cron_job = new cronJob;
        $cron_job->update_user_monitor_stats();

        return Command::SUCCESS;
    }
}
