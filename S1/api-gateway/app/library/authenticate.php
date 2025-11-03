<?php
namespace App\library;

use Throwable;
use GuzzleHttp\Client;


use GuzzleHttp\Cookie\CookieJar;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
use GuzzleHttp\Exception\ServerException;
use App\library\clientException as clientExceptionalHandler; 
use App\library\serverException as serverExceptionalHandler; 
/**
 * Authenticate means verify the user
 * Authoziaed - means does he has the access to use this resource
 */
class authenticate {
    
    public static $SESSION_PREFIX = "apigateway:users:sessions:"; //+key is the sessionid
    public function __construct () {

    }
    /**
     * check the token with redis cache
     * if not the checking happen with user-service 
     */
    public static function isAuthenticated ($headers) {

        $username = Cookie::get("username");
        $session_id = Cookie::get("sessionID");
        $session_token = Cookie::get("sessionToken");
        if(!$session_id || !$session_token ||!$username) {
            throw new clientExceptionalHandler(ErrorCode:"1700",code:401);
        }

        $session = Redis::hgetAll(authenticate::$SESSION_PREFIX.$session_id);
        if(!empty($session)){
            if($session["session_token"] === (string) $session_token) {
                return true;
            }else {
                throw new serverExceptionalHandler(ErrorCode:"1207",code:401);
            }
        }
        //by default if there any excepiton throw outsider forwarReq.php will caught those because this is inside their ty catch of guzzle
        
        $auth_api = env('AUTH_API');
        $endpoint = new Client([
            "timeout"  => 10.0,
            "cookies" =>true,
        ]);
        $resp = $endpoint->request(
            "POST",
            $auth_api,
            [
                "headers"=>$headers,
            ]
        );
        // var_dump($headers);
        $exists = method_exists($resp,'getBody');
        if($exists) {
            // var_dump($resp->getHeaders());
            $response = (string)$resp->getBody();
            $data = json_decode($response);
            $data = (array) $data;
            if($data["flag"] == 1) {
                //"Auth succeed ,further store it in gatewaat redis instance";
                authenticate::cacheSession($session_id,$session_token,$username);
                return true;
            }else { //fallback
                throw new serverExceptionalHandler(ErrorCode:"1700",code:401);
            }
                
        }else {
            throw new serverExceptionalHandler(ErrorCode:"2003",code:500);
        }
        return false;  //fallback
       

    }
    public static function cacheSession($session_id,$session_token,$username = "temp") {
        Redis::hmset(authenticate::$SESSION_PREFIX.$session_id,[
            "username" => $username,
            "session_token" => $session_token,
        ]);
        Redis::expire(authenticate::$SESSION_PREFIX.$session_id,3600);

    }

    public function Authorization() { //authozing the api gateway with services

    }
}

class auth {
    public static $session_id;
    public static $session_token;
}
?>

