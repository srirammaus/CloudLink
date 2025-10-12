<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\library\requestMonitor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
/**
 * 
 * This is a global middleware which intercepts all request and add to redis , then automtically forwareded to promethies
 */
class reqMonitorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = [
            "message"=>"Bad Request",
            "ErrorCode"=>"2701",
        ];
        $response = json_encode($response);
        $services = ["user","platform","notification","weather","unifiedWeather","dashboard"];
        $path =  $request->path();
        $ip = $request->ip();
        $service =  explode("/",$path);
        $service = $service[1] ?? NULL;
        if(!in_array($service,$services) && $service != "metrics"){
            Log::error("message".$service);
            return response($response,400);
        }
        $request->attributes->set("__path__",$path);
        $request->attributes->set("__service__",$service);

        return $next($request);
    }
    public function terminate(Request $request,Response $resp){

        $code = $resp->getStatusCode();
        $path =  $request->path();
        $ip = $request->ip();
        $service =  explode("/",$path);
        $service = $service[1] ?? NULL;
        $requestMonitor =  new requestMonitor($request);
        
        //config the prom storage
        $requestMonitor->configPromStorage();
        
        //add the req count 
        if ($service !=  "metrics" &&  $ip != null  && $path != null && $service != null && $code != null){
            
            $requestMonitor->putHttpReqCount ($ip,$service ,$path,$code);
        }
    }
}
