<?php 

/**
 * The sanitization takes placae inside the library
 * Exception -  when thrown by  "throw" syntax
 * Error - when thrown like fatal error or syntax error
 * Throwable - includes both any can be caught
 * while using namespaces PHP thinks any object you are using here should be belongs this namespace example if you are using "Throwable" then php thinks "\API\Throwable" but in reality it doesnt exist so you can do like "\Throwable" or "use Throwable"
 *  error_reporting(E_ERROR | E_PARSE); - for skipping warning
 * set_exception_handler(?$callback callback) used for uncaugh exception and erro handling , that should be in always top then only it could catch all uncaught exception but here now there is no other option so i ahve to put logg() function above the set_Exception_hanler;
 */
namespace API;
use Throwable;
/**
 * This Erorr code should me 2000
 * actaully the getMessage should be "Unknown Error occured"
 * $e->getmessage should be logged
 *  */
include_once "library/log.lib.php";
use function \library\logg;
session_start();
// $_SESSION["test"] = "test";
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
include_once __DIR__."/library/Authorization.lib.php";
include_once __DIR__."/library/general.lib.php";
// echo $_SESSION["test"];
$REST = new \RESTapi\REST;
/**
 * main -
 * try - 
 * catch -  whereever the catch happens at the come here and send through here
 */
function main() {
    global $REST;
    try{    
        if($REST->getRequestMethod() == "GET") {

            $genLib = new \library\genLib;
            $validparams = ["service"];
            $requestParams = $REST->_request;
            //check there are valid parameters
            if($genLib->isValidParams($validparams,$requestParams)){
                
                //check if the user session is valid or expired
                    //validate the old pwd
                $username =$_COOKIE["username"];
                $service = $requestParams["service"];
                $Auth = new \library\Authorization;
                $Auth->genJWT($username,$service);
                $Auth->setAuthHeader();

                $resp = [
                    "flag" => "1",
                    "message"=> "successfully Authorize",
                ];
                $REST->response(statusCode:200,flag:"1",message: $resp); 
            }else {
                throw new \clientException(ErrorCode:"1904");
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