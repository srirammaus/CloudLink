<?php
/**
 * Gateway Cache short TTL
 * Service Cahce long TTL
 * 
 */
namespace utils;

include_once __DIR__."/../config/redis_conn.php";
require_once __DIR__."/../vendor/autoload.php";
include_once __DIR__."/../library/log.lib.php";
use function \library\logg;


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
    public $redis;

    public function __construct() {
        try {
            $this->redis = $this->redisConn();
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2503");


        }
    }
    public function redisConn () {
        $redis = new \db\redis_conn;
        return $redis->redisConn();

    }
    public function loadSampleData () {
 
    }
    /**
     * not needed for all , we goona use it raley , becuase most of the GETTERS give empty or false as response so no prlm
     * we need it when something stored very long in redis , we need to check the existence before set it , because 
     * sometimes after expiration we set new is good way, but also not all time . we can also set again again not a big rpblm  
     * $redis->exists($key) (which maps to the Redis EXISTS command) works for all key types in Redis:
    * Strings (GET)
    * Lists (LPUSH, LPOP)
    * Sets (SADD, SMEMBERS)
    * Sorted Sets (ZSETs) (ZADD, ZRANGE)
    * Hashes (HSET, HGETALL)
     */
    public function isCached ($key,$prefix ="default") {
        try {
            $prefix .=":";
            return $this->redis->exists($prefix.$key);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");

        }
    }
    public function setExpiry ($key ,$expiry, $prefix) {
        $prefix .=":";
        return $this->redis->expire($prefix.$key,$expiry);
        
    }
    /**
     * for caching cookies we have to configure
    */
    public function setStringCache(string $key ,array $values,int $expiry,string $prefix = "default") {
        try{
            $prefix .=":";
            $input_val= "";
            foreach ($values as $idx => $val) {
                $input_val .= (string) $val; //type casting
            }
            return $this->redis->set($prefix.$key,$input_val);    

            // $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");

            
        }
   


    }
    public function appendStringCache (string $key ,array $values,int $expiry,string $prefix = "default") {
            
        try {
            $prefix .=":";
            $input_val= "";
            foreach ($values as $idx => $val) {
                $input_val .= (string) $val; //type casting
            }
            $this->redis->append($prefix.$key,$input_val);   
            // $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function getStringCache(string $key,string $prefix = "default") :string {
        try {
            $prefix .=":";
            return $this->redis->get($prefix.$key)?? "";

        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }

    }
    /**sAdd - Add one or more members to a set
        sCard - Get the number of members in a set
        sDiff - Subtract multiple sets
        sDiffStore - Subtract multiple sets and store the resulting set in a key
        sInter - Intersect multiple sets
        sInterStore - Intersect multiple sets and store the resulting set in a key
        sIsMember - Determine if a given value is a member of a set
        sMembers - Get all the members in a set
        sMove - Move a member from one set to another
        sPop - Remove and return one or more members of a set at random
        sRandMember - Get one or multiple random members from a set
        sRem - Remove one or more members from a set
        sUnion - Add multiple sets
        sUnionStore - Add multiple sets and store the resulting set in a key
        sScan - Scan a set for members 

        by default this is unsorterd, unorderd, unique
    */
    public function setSetCache (string $key ,array $values,int $expiry,string $prefix = "default") { //unsorted , unique , unordered
        try {
            $prefix .=":";
            foreach ($values as $idx => $val) {
                $this->redis->sAdd($prefix.$key,$val); 
            }
            $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public  function getSetCache (string $key,string $prefix = "default") {
        try {
            $prefix .=":";
            return $this->redis->sMembers($prefix.$key)?? [];
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function getSetExistence(string $key,string $prefix = "default") {

    }
    /**bzPop - Block until Redis can pop the highest or lowest scoring member from one or more ZSETs.
        zAdd - Add one or more members to a sorted set or update its score if it already exists
        zCard - Get the number of members in a sorted set
        zCount - Count the members in a sorted set with scores within the given values
        zDiff - Computes the difference between the first and all successive input sorted sets and return the resulting sorted set
        zdiffstore - Computes the difference between the first and all successive input sorted sets and stores the result in a new key
        zIncrBy - Increment the score of a member in a sorted set
        zInter - Intersect multiple sorted sets and return the resulting sorted set
        zinterstore - Intersect multiple sorted sets and store the resulting sorted set in a new key
        zMscore - Get the scores associated with the given members in a sorted set
        zPop - Redis can pop the highest or lowest scoring member from one a ZSET.
        zRange - Return a range of members in a sorted set, by index
        zRangeByScore, zRevRangeByScore - Return a range of members in a sorted set, by score
        zRangeByLex - Return a lexicographical range from members that share the same score
        zRank, zRevRank - Determine the index of a member in a sorted set
        zRem - Remove one or more members from a sorted set
        zRemRangeByRank - Remove all members in a sorted set within the given indexes
        zRemRangeByScore - Remove all members in a sorted set within the given scores
        zRevRange - Return a range of members in a sorted set, by index, with scores ordered from high to low
        zScore - Get the score associated with the given member in a sorted set
        zUnion - Add multiple sorted sets and return the resulting sorted set
        zunionstore - Add multiple sorted sets and store the resulting sorted set in a new key
        zScan - Scan a sorted set for members 

        consider this as indexed array but not actually a index array
        this output is based on rank . take example in school 1strank 2nd rank classified by marks(score)  
    */
    public function setZSetCache (string $key ,array $values,int $expiry,string $prefix = "default") { //sorted , orderd duplicate allowed
        try {
            $prefix .=":";
            foreach ($values as $idx => $val) {
                $this->redis->zAdd($prefix.$key,$idx, $val); 
            }
            $this->redis->expire($prefix.$key,$expiry);

        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function getZSetCache (string $key,$min_rank=0,$max_rank=-1,string $prefix = "default") {
        try{
            $prefix .=":";
            return $this->redis->zRange($prefix.$key,$min_rank,$max_rank) ?? []; // 0 - generally means lowest rank ,-1 generally means highest rank
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    
    }
    public function getZSetCacheWithScore(string $key,$min_score,$max_score,$prefix="default") {
        try {
            $prefix .=":";
            return $this->redis->zRangeByScore($prefix.$key,$min_score,$max_score) ?? [];

        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }

    }
    public function setHashCache (string $key ,array $values,int $expiry,string $prefix = "default") {
        try {
            $prefix .=":";
            $item_key = array_keys($values)[0];
            $item_val = $values[$item_key];
            $this->redis->hSet($prefix.$key,$item_key, $item_val); 
            // $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }

    public function getHashCache(string $key,$item_key,string $prefix = "default") {
        try {
            $prefix .=":";
            return $this->redis->hGet($prefix.$key,$item_key) ?? [];
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function setAllHashCache (string $key ,array $values,int $expiry,string $prefix = "default") {
        try {
            $prefix .=":";
            return $this->redis->hMSet($prefix.$key,$values); 
            // $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function getAllHashCache (string $key,string $prefix = "default") {
        try {

            $prefix .=":";
            return $this->redis->hGetAll($prefix.$key) ?? [];
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
         }
    }
    public function delCache ($key,$prefix = "default") {
        try {
            $prefix .=":";
            return $this->redis->del($prefix.$key) ?? [];
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function setListCache (string $key ,array $values,int $expiry,bool $head_tail,string $prefix = "default") {
        try {
            $prefix .=":";
            if($head_tail === true) { //right push
                foreach ($values as $idx => $val) {
                    $this->redis->rPush($prefix.$key, $val); 
                }
            }else { //left push
                foreach ($values as $idx => $val) {
                    $this->redis->lPush($prefix.$key, $val); 
                }
            }
            $this->redis->expire($prefix.$key,$expiry);
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }


    }
    public function getListCache (string $key,$head_tail,string $prefix = "default") {
        try {
            $prefix .=":";
            if($head_tail === true) { //right popo
                $this->redis->rRange($prefix.$key, $start, $stop); /* ['A', 'A', 'C', 'B', 'A'] */
            }else { //left Rem
                $this->redis->lRange($prefix.$key, $start, $stop); /* ['A', 'A', 'C', 'B', 'A'] */

            }
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }

    // $redis->lRange('key1', 0, -1); /* ['A', 'A', 'C', 'B', 'A'] */
    // $redis->lRem('key1', 'A', 2); /* 2 */
    // $redis->lRange('key1', 0, -1); /* ['C', 'B', 'A'] */
    public function rmItemFromListCache (string $key ,$item, $count, bool $head_tail,$prefix = "default") {
        try {
            $prefix .=":";
            if($head_tail === true) { //right popo
                $this->redis->rRem($prefix.$key,$item,$count); 
            }else { //left Rem
                $this->redis->lRem($prefix.$key,$item,$count); 
            }
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function popFromListCache(string $key , bool $head_tail,$prefix = "default") {
        try {
            $prefix .=":";
            if($head_tail === true) { //right popo
                $this->redis->rPop($prefix.$key); 
            }else { //left Pop
                $this->redis->lPop($prefix.$key); 
            }
        }catch (\RedisException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \cacheException(ErrorCode:"2502");
            
        }
    }
    public function flushCache () {
        return $this->redis->flushDb();
    }

    /**
     * 
     * adding , stcutrually this is a sorted set
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return void
     */
    public function geoAdd(string $key, string $value, int $ttl = 0) {
    }
    /**
     * 
     * Search by radius of 5km or 43km
     * @return void
     */
    public function geoSearch(){

    }



}
// $cache = new cachelib;
// $cache->redisConn();
// $cache->loadSampleData();
// $cache->setZSetCache("user_sessions",[
//     "0"=>"sriram",
//     "1"=>"root",
//         "7"=>"admin",
//         "11"=> "adam",
// ],30);
// $c = $cache->getZSetCache("user_sessions");
// // $c = $cache->getZSetCacheWithScore("user_sessions",0,9);

// var_dump($c);



?>