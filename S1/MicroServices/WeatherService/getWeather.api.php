<?php
namespace API;
use library\getWeather;
use Throwable;

include_once __DIR__."/library/log.lib.php";
use function \library\logg;

set_exception_handler (function(Throwable $e) { 

    logg(file:"server_err", exception_:$e);
    $packedMessage = [
        "file"=>$e->getFile(),
        "line"=>$e->getLine(),
        "ErrorCode"=> "2000",
        "message"=>"Unknown Error Occurred", 
    ];
    echo json_encode($packedMessage);

}); 
error_reporting(E_ERROR | E_PARSE);

require __DIR__."/vendor/autoload.php"; // autoload contains the utils files // below file which are reaching through this file (signup.api.php) can use this autloaded files
include_once __DIR__."/REST.api.php";
include_once __DIR__."/library/ExceptionHandler.php";
include_once __DIR__."/library/general.lib.php";
include_once __DIR__."/library/getWeather.lib.php";
$REST = new \RESTapi\REST;

function main  () {
    global $REST;
    try {
        if($REST->getRequestMethod() == "GET") {
            $genLib = new \library\genLib;
            $validparams = ["latitude","longitude","location"];
            $optional = 1;
            $requestParams = $REST->_request;
            $isValidParams = $genLib->isValidParams($validparams,$requestParams,$optional);
            if($isValidParams == true || $isValidParams == NULL){
                
                $lat = $requestParams["latitude"];
                $lng = $requestParams["longitude"];
                $location   = $requestParams["location"];
                if(isset($lat) && isset($lng) || isset($location)){
                    // echo (float) $lat;
                    $params = $genLib->sanitizedCoordinates($requestParams);
                    $getWeather = new getWeather();
                    $getWeather->searchWeather($params);
                
                    // var_dump($params);
                    
                }else {
                    throw new \clientException(ErrorCode:"1904");
                }
                //check if the user session is valid or expired
 
              
            }else {
                throw new \clientException(ErrorCode:"1904");
            }

        }else {
            throw new \clientException(ErrorCode:"2100",code:400);
        }

    

    }catch(\PDOException $e) { //This is more subclassification as of now it is okay
        logg(file:"db_err",exception_: $e);
        throw new \databaseException(ErrorCode:"1803");
    }catch (\Throwable $e) {
        if($e instanceof \clientException) {
            logg(file:"client_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());
            // header("Location: srirammaus.github.io");

        }
        elseif ($e instanceof \databaseException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }else if($e instanceof \serverException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }
        else if($e instanceof \verificationException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }
        else { //
            logg(file:"server_err", exception_:$e);
            $REST->response($e->getCode(),"2003","Internal Server Error"); //here message should be internal error
        }

    }
}
main()

?>