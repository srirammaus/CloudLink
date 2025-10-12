<?php
namespace API;
use Throwable;


include_once "library/log.lib.php";
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


include_once "utils/cache.php";
session_start(); // start the session // This is used to store the captcha temporarily
    

error_reporting(E_ERROR | E_PARSE);

require __DIR__."/vendor/autoload.php"; 
include_once "REST.api.php";
include_once "library/ExceptionHandler.php";
include_once "utils/captcha.php";

$REST = new \RESTapi\REST;
/**
 * For Fallback we can also use the deault image , as you know now a image is send as deafult 
 * but for industry standard and prdouction it is better to use json response
 */
function main  () {
    global $REST;
    try{
        if($REST->getRequestMethod() == "GET") {
            $img = \utils\captcha::generateCaptcha();
            
            //setting the header before sending, becuase by deafult it is different 
            // we have to put this top because eventhough exception hanler caught the error , but this api 
            //supposed to send image only , if your front end ready to check the response like contetype with if else , we can put here
            header('Content-Type: image/png'); 
            header("Access-Control-Allow-origin:http://localhost/");
            
            //sending or presenting the image as png
            imagepng($img);

            //destroy the image
            imagedestroy($img);
            
        }else {
            throw new \clientException(ErrorCode:"2100",code:400);
        }

    }catch(\Throwable $e) {
        //im gonna use response function here because what if the error occured before the header(img/pn) set ?
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

        }
        else { //
            logg(file:"server_err", exception_:$e);
            $REST->response($e->getCode(),"2003","Internal Server Error"); //here message should be internal error


        }
    }

}
main();
