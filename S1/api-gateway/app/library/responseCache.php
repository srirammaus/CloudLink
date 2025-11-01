<?php 
namespace App\library;
define("CACHE_PREFIX","apigateway:response_cache:");
/**
 * This below should be extended as Middleware
 */
use Clousre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;


class responseCache {
    
    public $path_to_key;
    public $request;
    public $response;
    public function __construct () {
        $this->path_to_key;

    }
    public function handle (Request $request, $next) {
        
        return $next($request);
    }
    /**
     * Global cache - avail for all
     * User specific -  with session or token
     * 
     * add feature here that, if any updates happens in anyinternal service and we must have to delete that in our redis cache, which works by giving header instruction key
     * if some 
     */
    public function terminate (Request $request, Response $response) {
        $path = $request->path();
        $this->request = $request;   
        $this->response = $response;
        $resp = $this->response;
        $this->process_cache($path,$resp);
     
        
    }
    /**
     * Resp is here is custom
     */
    public function process_cache ($path,$resp) {
        info($path);
        if(in_array($path,$this->allowedCachePaths)) {
            $path = explode("/",$path);
            $path = implode("_",$path);
            $session_caching_key = $this->request->cookie('sessionID') ?? NULL; //session id is the caching key
           
            //setting the cache only for those who having sessions
            if($session_caching_key != NULL) {
                $this->path_to_key = CACHE_PREFIX.$path.":".$session_caching_key;
                if(method_exists($resp,'getContent') ){ //&& method_exists($resp,"getHeadrLine")
                    $CacheControl = $resp->getHeaderLine("X-Cache-Control") ?? "None";
                    switch ($CacheControl) {
                        case 'Set':
                            # code...
                            $this->setCache($resp);
                            break;
                        case 'Del':
                            # code...
                            $this->removeCache($resp);
                            break;
    
                        case 'None':
                            # code...
                            break;
                        case 'Update':
                            $this->updateCache($resp);
                            # code...
                            break;    
                        default:
                            # code...
                            break;
                    }
                }

        
            }
        }
        else if (in_array($path,array_keys($this->allowedAgCachePaths))) {
            #recursion
            $resp_arr =$this->request->attributes->get("response_arr");
            // var_dump(array_keys($resp_arr));
            foreach ($this->allowedAgCachePaths[$path] as $key => $value) {
                # code...
                $resp = $resp_arr["/".$value] ?? NULL;
                if ($resp) {
                    $this->process_cache($value,$resp);      
                }
            }
        }

    }
    /**
     * check if that in allowedcahcpaths and check the status code and check the custom header
     */
    public function setCache ($resp) {
        // var_dump($resp->getHeaderLine("X-Cache-Control") ?? "NOT SET");
        if(!Redis::exists($this->path_to_key)   ){
            if($resp->getStatusCode()  == 200) {
                $ttl = (int) $resp->getHeaderLine("X-Cache-TTL") ??600;
                Redis::set($this->path_to_key,$resp->getContent());
                Redis::expire($this->path_to_key,$ttl);
            }
        }
    }
    /**
     * check if that request path is in cacheAllowedPaths and check the status code and check custom header for cache clear
     */
    public function removeCache ($resp) {
        if($resp->getStatusCode() == 200) {
            Redis::del($this->path_to_key);
        }

    }
    /**
     * update should happen if there is existing data 
     */
    public function updateCache ($resp) {
        if($resp->getStatusCode() == 200) {
            $ttl = (int) $resp->getHeaderLine("X-Cache-TTL") ??600;
            if(Redis::exists($this->path_to_key)){
                Redis::set($this->path_to_key,$resp->getContent());
                Redis::expire($this->path_to_key,$ttl);
            }else { //else set
                Redis::set($this->path_to_key,$resp->getContent());
                Redis::expire($this->path_to_key,$ttl);
            }
        }
    }
  

}

?>