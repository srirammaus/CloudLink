<?php
/**
 * Gateway Cache short TTL
 * Service Cahce long TTL
 * 
 */
namespace utils;

include_once __DIR__."/../config/redis_conn.php";
require_once __DIR__."/../vendor/autoload.php";

//settting the env 
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",["redis_cred.env",".env"]);

//loading the env
$dotenv->load();

//intializing the session handler to redis // im gonna put this php.ini file
// ini_set("session.save_handler","redis");
// ini_set("session.save_path","tcp://ubuntu.server:6379?auth={$_ENV['REDIS_PASS_1']}");  ///by default it connected to user "default"
 

/**
 * Storage : redis
 * DB cache setter and getter . 
 * redis should be centralized ..
 * because it is caching. 
 * regional centralization US(central redis) india (central redis)
 * There is no need of cahcing needs here
 */
class cachelib  {
    public function __construct() {
        

    }
    public function redisConn () {
        $redis = new \db\redis_conn;
        return $redis->redisConn();

    }
    public function loadSampleData () {
        $redis = $this->redisConn();
        $redis->set("key2","value");
    }
    public function isCached () {
        switch ($variable) {
            case 'value':
                # code...
                break;
            
            default:
                # code...
                break;
        }
    }
    public function cacheThis() {
        switch ($variable) {
            case 'value':
                # code...
                break;
            
            default:
                # code...
                break;
        }
    }
    /**
     * for caching cookies we have to configure
    */
    public function setSetCache() { //Normal set , unorderd
         
    }
    public function getSetCache () {

    }
    public function setListCache() {

    }
    public function getListCache () {

    }
    public function setHyperLogger () { // cardinality or count of set , with standard error 

    }
    public function getHyperLogger () {

    }

}
$cache = new cachelib;
$cache->redisConn();




?>