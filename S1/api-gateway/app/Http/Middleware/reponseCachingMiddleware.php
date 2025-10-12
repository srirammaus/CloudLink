<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\library\responseCache as Middleware;

class reponseCachingMiddleware extends Middleware 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public $allowedCachePaths = [
        "api/user/getUserInfo.api.php",
        "api/platform/tools.api.php",
    ];
    //for aggreagated
    public $allowedAgCachePaths = [
        "api/dashboard" => [
            "api/user/getUserInfo.api.php",
            "api/platform/tools.api.php",
        ],
    ]; 

}
