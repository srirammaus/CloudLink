<?php
namespace db;
// include __DIR__."/../libraryExceptionHandler.php";
// include_once __DIR__."/../library/log.lib.php";
require_once __DIR__."/../vendor/autoload.php";


class redis_conn {


    private $hostname ;
    private $username ;
    private $password ;
    private $db ;

    public function redisConn () {
        //setting timeout
        $timeout = 2;
        //setting env and loading the env
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",["redis_cred.env",".env"]);
        $dotenv->load();
        
        //setting the env variable to local variabels
        $this->hostname = $_ENV["REDIS_HOSTNAME"];
        $this->username = $_ENV["REDIS_USER_1"];
        $this->password = $_ENV["REDIS_PASS_1"];
        $this->port = $_ENV["REDIS_PORT"];

        //creating redis obj
        $redis = new \redis;

        //connecting using pconnect
        $redis->pconnect($this->hostname,$this->port,$timeout);

        //authentication
        $redis->auth([$this->username,$this->password]);

        //returning redis object
        return $redis;
    }

}
?>