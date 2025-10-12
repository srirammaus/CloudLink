<?php
namespace db;
// include __DIR__."/../libraryExceptionHandler.php";
// include_once __DIR__."/../library/log.lib.php";
require_once __DIR__."/../vendor/autoload.php";


class redis_conn {
    public $redis;

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
        $this->redis = new \Redis;

        //connecting using pconnect //persistent connection once connected dont need to connect agai
        $this->redis->pconnect($this->hostname,$this->port,$timeout);

        //authentication
        $this->redis->auth([$this->username,$this->password]);

        //set the configuration redis
        $this->configRedis();

        //returning redis object
        return $this->redis;
    }   
    /**
     * These are all default configuration you can overwrite , if u need
     */
    public function configRedis() {
        //configuring redis 
        //seriazling all data not maully instead use the phpredis predefined OPT_SERIALIZER

        $this->redis->setOption(\Redis::OPT_SERIALIZER,\Redis::SERIALIZER_PHP);

        //setting my application name as prefix
        //this is the default prefix and create more prefix within this but if you create new prefix like Cloudlink , youhave to write the below syntax again with the new name and then connection should be new , dont worry u using pconnect no prblem
        $this->redis->setOption(\Redis::OPT_PREFIX,"CloudLink:");

        //scan - used for searching and filtering keys
        $this->redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_PREFIX); // \Redis::SCAN_RETRY - This is default 

        //maximum retries  for commads like get and set
        $this->redis->setOption(\Redis::OPT_MAX_RETRIES, 5);

        //backoff algorithm ,used for retries  to connection //all algorith used in redis backoff are depend on "base and cap" both value are in milliseconds
        //This maximun retries depends on the above MAX_RETRIES , you can also have the opiton to use OPT_BACKOFF_RETRIES
        //you can alos have doubt the exponential algorithm by default do 2 power 1 then 2 ppower 2 then 2 power 3 soo on 
        // then it follows base(500) x 2 power 1 , so like this so on , but if the answer croseed 750 cap , it wont works and every 750ms it tries to reconnect
        $this->redis->setOption(\Redis::OPT_BACKOFF_ALGORITHM,\Redis::BACKOFF_ALGORITHM_EXPONENTIAL);
        $this->redis->setOption(\Redis::OPT_BACKOFF_BASE,500); //first run after 500ms that is base
        $this->redis->setOption(\Redis::OPT_BACKOFF_CAP,800); //from the second attemp it tries once every 800ms

    }


}
?>