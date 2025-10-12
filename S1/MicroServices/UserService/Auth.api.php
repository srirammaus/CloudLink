<?php

namespace API;
use Throwable;
/***
 * This used for api gatewat , same fuctinalites happen i signup lib with additon work
 * 
 */


include_once "library/log.lib.php";
use function \library\logg;
session_start();

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

require __DIR__."/vendor/autoload.php"; // autoload contains the utils files // below file which are reaching through this file (signin.api.php) can use this autloaded files
include_once __DIR__."/REST.api.php";
include_once __DIR__."/library/ExceptionHandler.php";
include_once __DIR__. "/library/sessionManager.lib.php";
$REST = new \RESTapi\REST;

function main() {
    global $REST;
    try{    
        if($REST->getRequestMethod() == "POST") {

            $genLib = new \library\genLib;
    
                //create session Manager 
            $sessionManager = new \library\sessionManager;
            $loggedIn = $sessionManager->isAuthenticated();
            // var_dump($loggedIn);
            if($loggedIn) {
                //redirecting to dashboard
                
                $response_data = [
                    "flag" => "1",
                    "username"=> $loggedIn["username"],
                    "message"=> "login sucessfull ,refresh",
                ];
                $REST->response(statusCode:200,flag:"1",message: $response_data); 
            }else {
                
                throw new \clientException(ErrorCode:"1205",code:401);

            }
        }else {
            throw new \clientException(ErrorCode:"2100",code:400);

        }

    }catch(\PDOException $e) { //This is more subclassification as of now it is okay
        logg(file:"db_err",exception_: $e);
        throw new \databaseException(ErrorCode:"1803");
        
    }catch (\Throwable $e) { //add PDO
        if($e instanceof \clientException) {
            logg(file:"client_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }
        elseif ($e instanceof \databaseException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }else if($e instanceof \serverException) {
            logg(file:"server_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }else if($e instanceof \verificationException) {
            echo "This is where err cauhgt";
            logg(file:"client_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }
        else { //
            logg(file:"server_err", exception_:$e);
            $REST->response($e->getCode(),"2003","Internal Server Error"); //here message should be internal error
        }

    }


}
main();


?>