<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\library\ratelimiting;


use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;  

class LimitThrottler
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
            "message"=>"something went wrong",
            "ErrorCode"=>2000,
        ];
        try {
            $limitThrottle  = new ratelimiting($request);
            $limiter = $limitThrottle->limiter();
            if (!$limiter){ #further proceed
                throw new clientExceptionalHandler(ErrorCode:"1903",code:429);
            }
        }catch(\Throwable $e) {
            if($e instanceof serverExceptionalHandler ) {
                $response["message"] = $e->getCustomMessage();
                $response["ErrorCode"] = $e->getErrorCode();
                $code = $e->getCode();
                return $this->sendResponse($response,$code);
            }elseif ($e instanceof clientExceptionalHandler) {
                $response["message"] = $e->getCustomMessage();
                $response["ErrorCode"] = $e->getErrorCode();
                $code = $e->getCode();
                return $this->sendResponse($response,$code);
            }else {
                $code = 500;
                return $this->sendResponse($response,$code);
            }
        }
        // $limitThrottle  = new ratelimiting($request);
        // $limitThrottle->limiter(); 
        return $next($request);
    }

    public function sendResponse(array $message,int $code) {
        $message = json_encode($message);
        return response($message,$code);
    }
    public function terminate(){
        
    }
}
