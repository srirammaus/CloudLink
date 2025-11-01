<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReqAggregator;
use App\Http\Controllers\serviceController;
use App\Http\Controllers\MetricMonitorController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// wihtout alias directly implementing - Route::get("/user/{any}",[serviceController::class,'reqRoute'])->where('any','.*')->middleware(LimitThrottler::class);
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


//Monitorization using metric collector
Route::get("/metrics",[MetricMonitorController::class,'updateMetrics']);

//user service
Route::get("/user/{any}",[serviceController::class,'reqRoute'])->where('any','.*');//->middleware('limitThrottle');
Route::post("/user/{any}",[serviceController::class,'reqRoute'])->where('any','.*');// will use this as global->middleware('limitThrottle');

//platform service
Route::get("/platform/{any}",[serviceController::class,'reqRoute'])->where('any','.*');
Route::post("/platform/{any}",[serviceController::class,'reqRoute'])->where('any','.*');


//notification service
Route::get("/notification/{any}",[serviceController::class,'notification'])->where('any','.*');
Route::post("/notification/{any}",[serviceController::class,'notification'])->where('any','.*');


//weather service
Route::get("/weather/{any}",[serviceController::class,'reqRoute'])->where('any','.*');
Route::post("/weather/{any}",[serviceController::class,'reqRoute'])->where('any','.*');

//combined data [hyper local data provided by python]
Route::get("/unifiedWeather/{any}",[serviceController::class,'unifiedWeather'])->where('any','.*');
Route::post("/unifiedWeather/{any}",[serviceController::class,'unifiedWeather'])->where('any','.*');

//aggreation needed api's are below
//aggregated api request like dsahboard
Route::get("/dashboard",[ReqAggregator::class,'seperate_and_unify']);
// Route::post("/Dashboard/{any}",[ReqAggregator::class,'seperate_and_unify']);




