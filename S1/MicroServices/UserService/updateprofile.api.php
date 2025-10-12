<?php
namespace API;
use Throwable;
/**
 * This used under session token
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
include_once __DIR__."/library/updateProfile.lib.php";
include_once __DIR__."/library/general.lib.php";
include_once __DIR__."/library/sessionManager.lib.php";
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
            $validparams = ["new_name","new_username","new_email","new_secondary_email","new_phone_number","new_bio"];
            $optional = 5;
            $requestParams = $REST->_request;
            //check there are valid parameters
            $isValid = $genLib->isValidParams($validparams,$requestParams,$optional);
            if( $isValid == True || $isValid == NULL ){
                //check if the user session is valid or expired
                $sessionManager = new \library\sessionManager;
                if($sessionManager->isAuthenticated()){
                    var_dump($requestParams);
                    $update_profile = new \library\updateProfile($requestParams);
                    if($update_profile->updateProfile()){
                        $resp = [
                            "flag" => "1",
                            "message"=> "Changes Applied",
                        ];
                        $REST->response(statusCode:200,flag:"1",message: $resp); 
                    }
                    
                }else {
                    throw new \clientException(ErrorCode:"2400");
                }
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