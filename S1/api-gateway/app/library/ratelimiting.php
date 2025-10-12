<?php
namespace App\library;
define('RATELIMITPREFIX',"apigateway:users:ratelimit:");
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;  
/**
 * 
 * some types of request should not reate liited or threotled that are based on the request types or if you want ot do that in request params alos no prblm
 * This should be attached to the middleware
 * This should use the redis db
 * here gonna follow token bucket algorith with refiller rate 1 token for 2 seconds . generally refiller rate 1 token 
 * key problem :Does the rate limiter ,throttle API requests based on IP, the user ID, or other properties?   
*/
class ratelimiting {
    

    public function __construct($request) {
        $this->request = $request;
    }
    public function getService() {
        $service = $this->request->path();

        // Split path by "/"
        $serviceParts = explode("/", $service);
        // print_r($serviceParts);

        // Join with ":"
        $service = implode(":", $serviceParts);
        // var_dump($service); // e.g., "api:user:list"

        return $service ;

    }
    public function getIP () {
        return $this->request->ip();
    }
    /**
     * Limit based on the request
     * else design depends on you drop or pause throttle
     * 
     * 
     * put in db using IP
     * 
     * 
     * test using ab -n 20 -c 5 http://localhost:8000/api/test  --  this is apaceh bench comes along with xampp
     */

    public function limiter () {
        try {
            $key = $this->getIP().":".$this->getService();
       
            // Log::error($key);

            // $refiller = 1;
            // $Bucket;
            $max = 4;
            $timestamp = time();
    
            $existed = Redis::hgetAll(RATELIMITPREFIX.$key);

            if (empty($existed)) {
                // first request: bucket starts full
                $token_count = $max - 1; // consume immediately
                $last_time = $timestamp;
            }else {
                $token_count = $existed['token_count'];
                $last_time  = $existed['timestamp'];

                $elapsed_time = $timestamp - $last_time;
                
                // refill based on elapsed seconds
                // if ($elapsed_time >= 10) {  it means every 10 sec after token is refilled , but anyhow capped by 4 note that
                $token_count = min($max, $token_count + $elapsed_time);
                // }    
                
                // consume 1 token
                $token_count--;
                // check if request should be blocked
                // future you can also throttle
                if ($token_count < 0) {
                    // return response("Too many requests", 429);
                    return False;
                }
            }
    

            // save new state
            Redis::hmset(RATELIMITPREFIX.$key, [
                "token_count" => $token_count,
                "timestamp"   => $timestamp,
            ]);

            if (empty($existed)) {
                Redis::expire(RATELIMITPREFIX.$key, 60);
            }
            
        }
        catch (\Throwable $e) 
        {
                throw new serverExceptionalHandler(ErrorCode:"2000",code:500);
        }
        return True;

      

    }
    public function __destruct() {

    }
    
}
?>