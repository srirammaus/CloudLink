<?php
namespace API;
use Throwable;
/***
 * This comes under forgotPwd.lib.php , this is for without login
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
include_once "REST.api.php";
include_once "library/ExceptionHandler.php";
include_once "library/forgotPwd.lib.php";
include_once "library/general.lib.php";
include_once "library/sessionManager.lib.php";
$REST = new \RESTapi\REST;
function main() {
    global $REST;
    /**
     * validate the parameters , here the params are username, old password and new password
     * check isAutheticated before any request
     * validate the old pwd
     * add the new pwd
     */
    try{    
        if($REST->getRequestMethod() == "POST") {

            $genLib = new \library\genLib;
            
            //Anyone of the paramter should be allowed //so optional is 2
            $validparams = ["username","email","phone"];

            $requestParams = $REST->_request;
            $requestParamsKeys = array_keys($requestParams);

            $optional = 2;
            //check there are valid parameters

            if($genLib->isValidParams($validparams,$requestParams,$optional) === true){
                $phone = $requestParams["phone"]??NULL;
                $email = $requestParams["email"]??NULL;
                $username = $requestParams["username"]??NULL;
  
                $forgotPwd = new \library\forgotPwd;
                if($forgotPwd->forgotPassword($username,$email,$phone)) {
                    //if the condition true // we can send the reset URL

                    $resp = $forgotPwd->sendResetURL();
                    if($resp) {
                        $REST->response(statusCode:200,flag:"1",message: $resp); //flag will be come with $resp
                        
                    }else {
                        throw new \serverException(ErrorCode:"2000");
                    }
                }else {
                    throw new \serverException(ErrorCode:"2000");
                }

            }else {
                throw new \clientException(ErrorCode:"1907");

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