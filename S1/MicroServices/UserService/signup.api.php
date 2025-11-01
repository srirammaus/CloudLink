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
include_once "REST.api.php";
include_once "library/ExceptionHandler.php";
include_once "library/signup.lib.php";
// echo $_SESSION["test"];
$REST = new \RESTapi\REST;
/**
 * main -
 * try - 
 * catch -  whereever the catch happens at the come here and send through here
 */

function main  () {
    global $REST;
    try {
        if($REST->getRequestMethod() == "POST") {
            $signup = new \library\signup;
            logg(file:"backend_log", message: json_encode($REST->_request));

            $resp = $signup->createUser ($REST->_request);
            if($resp) {
                /**
                 * The flag going from anything.api.php is generaly 1 and respected messages has been sent , if message having sepereate content it can also have sperate flag
                 */
                $REST->response(statusCode:200,flag:"1",message:$resp);
            }else {
                throw new \databaseException(ErrorCode:"1804");
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
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage()); //
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