<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\EndpointData;
use Illuminate\Console\Command;

class InsertNewApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:addEndpoint {name} {--baseURL=} {--path=} {--method=} {--port=} {--status=} {--auth=} {--version_=} ';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserting new endpoint data to db';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * while using --something= You can also use hasArgument to check whether the args is existed or not 
         */
        $name = $this->argument("name"); 
        $baseURL = $this->option("baseURL")  ?? NULL;
        $path = $this->option("path") ?? NULL; //This is very crucial input , while using thi cmd typer must type /api/user or /api/service whatever the path should be there
        $method = $this->option("method") ?? NULL;
        $port = $this->option("port") ?? NULL;  //not null you have use 80 as default
        $status = $this->option("status") ?? 1;
        $auth = $this->option("auth") ?? '0';
        $version = $this->option("version_") ?? 'v1';

       
        // echo $name.$baseURL.$path.$method.$port.$status.$auth.$version;
        $endpoint_data =  new EndpointData;
        if($endpoint_data) {
            $endpoint_data->name = $name;
            $endpoint_data->baseURL = $baseURL;
            $endpoint_data->path = $path;
            $endpoint_data->method = $method;
            $endpoint_data->port = $port;
            $endpoint_data->status = $status;
            $endpoint_data->auth = $auth;
            $endpoint_data->version = $version;
            if($endpoint_data->save()) {
                return Command::SUCCESS;

            }
        }
        echo "FAILED";
        return Command::FAILED;
    }
}
