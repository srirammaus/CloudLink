<?php
/**
 * This api can be used for 
 * existingData  - return status`,verified_E,verified_M,email_token_expiry,phone_otp_expiry
 * requesting new OTP
 * request new Token
 * verificaiton of OTP 
 * verication of Token
 */
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
error_reporting(E_ERROR | E_PARSE);


require __DIR__."/vendor/autoload.php"; // autoload contains the utils files // below file which are reaching through this file (signin.api.php) can use this autloaded files
include_once "REST.api.php";
include_once "library/ExceptionHandler.php";
include_once "library/verification.lib.php";
include_once "library/general.lib.php";
include_once "library/sessionManager.lib.php";
$REST = new \RESTapi\REST;

function main() {
    global $REST;
  try {
        $genLib = new \library\genLib;
        $validparams = ["username","newotp","newtoken","email_token","phone_otp"];
        $optional = 3; 
        $requestParams = $REST->_request;
        $requestParamsKeys = array_keys($requestParams);

        //check there are valid parameters
        if($genLib->isValidParams($validparams,$requestParams,$optional) === true){
            $verification =  new \library\verification;
            
            $username = $requestParams["username"];
            switch (true) { 

                //Now temporarly activate the account via email
                case in_array("email_token",$requestParamsKeys): //GET

                    if($REST->getRequestMethod() == "GET") {
                        //verifying the user sent email token and redirects him to the dashboard page or login page
                        //302

                        //check the existing status and load the respected params to varible .NOTE: load what need
                        $existingData = $verification->existingData($username);

                        //this should be from actually db
                        $status = $existingData["status"]; // For temporary i'm activating the active status through email
                        $verified_E = $existingData["verified_E"];
                        $verified_M = $existingData["verified_M"];

                        $pending_email = $existingData["pending_email"];
                        $email_token = $requestParams["email_token"];
                        
                        //checking the existence email verification , if not proceed
                        if($verified_E == 0) { 

                            $verifyToken = $verification->verifyToken($username,$email_token);
                            //check both the tokens are same
                            if($verifyToken)  {

                                //update the active status temporary by using email
                                $new_status = 1;  //This has to be as it was . $status
                                $new_verified_E = 1;
                                $new_verified_M = $verified_M; // This is should be what it was, so i put like this, because it is mobile number verification, this func doesn't kno whether that the mobile number verified or not , so we put what that was

                                //This should be consider as activate because ,as of now we using mail to activate
                                $verifyMail = $verification->updateStatus($username,$new_status,$new_verified_M,$new_verified_E,$pending_email,NULL);
                            
                                if($verifyMail) {   
                                //redirection  and redirection URL must contain status = success, which should again cross check whether the verified_E is 1 and temporly check status is 1
                                    $resp = [
                                        "flag"=>"1",
                                        "Message"=>"Email verified succesfully",
                                    ];
                                    $REST->response(statusCode:200,flag:"1",message: $resp);  
                                }else {
                                    throw new \databaseException(ErrorCode:"1803");
                                }
                            }else {
                                throw new \serverException(ErrorCode:"2000");
                            }
                        
                        }else { // if this was 1 already the email has been verfied redirect him to login page or dashboard page
                            throw new \clientException(ErrorCode:"1206");
                        }
                    }else {
                        throw new \clientException(ErrorCode:"2100",code:400);

                    }

                    break;
                                // verify with OTP
                case in_array("phone_otp",$requestParamsKeys): //POST

                    if($REST->getRequestMethod() == "POST") {

                        //verifying the user sent OTP  and redirects him to the dashboard page or login page
                        //302

                        //check the existing status and load the respected params to varible .NOTE: load what need
                        $existingData = $verification->existingData($username);

                        $status = $existingData["status"]; // Activation compulosrily made from mobile number
                        $verified_M = $existingData["verified_M"];
                        $verified_E = $existingData["verified_E"];

                        $pending_phone = $existingData["pending_phone"];
                        $phone_otp = $requestParams["phone_otp"];

                        if($verified_M == 0) { 
                                
                            //check both the OTP are same
                            $verifyOTP = $verification->verifyOTP($username,$phone_otp);
                            if($verifyOTP)  {

                                $new_status = 1;
                                $new_verified_M = 1;
                                $new_verified_E = $verified_E; // This is should be what it was, so i put like this, because it is email verification, this func doesn't kno whether that the email verified or not , so we put what that was
                                
                                //It activates the user account
                                $verifyPhone = $verification->updateStatus($username,$new_status,$new_verified_M,$new_verified_E,NULL,$pending_phone);
                            
                                if($verifyPhone) {   
                                //redirection  and redirection URL must contain status = success, which should again cross check whether the verified_E is 1 and temporly check status is 1
                                    $resp = [
                                        "flag"=>"1",
                                        "Message"=>"sucessfully activated",
                                    ];
                                    $REST->response(statusCode:200,flag:"1",message: $resp);  
                                }else {
                                    throw new \databaseException(ErrorCode:"1803");
                                }
                            }else {
                                throw new \serverException(ErrorCode:"2000");
                            }
                        

                        }else { // if this was 1 already number verfied redirect him to login page or dashboard page
                            throw new \clientException(ErrorCode:"1307");
                        }
                    }else {
                        throw new \clientException(ErrorCode:"2100",code:400);

                    }
                        break;
                //ver
                case in_array("newotp",$requestParamsKeys): //POST
                    if($REST->getRequestMethod() == "POST") {

                        //requesting for new verification OTP to activate the phone number
                        
                        //check the existing status and load the respected params to varible .NOTE: load what need
                        $existingData = $verification->existingData($username);

                        $status = $existingData["status"];
                        $verified_M = $existingData["verified_M"];
                        $verified_E = $existingData["verified_E"];
                        $pending_phone = $existingData["pending_phone"];

                        if($status == 0) {
                            //proceed further
                            $resp = [
                                "flag"=> "1",
                                "message"=>"OTP sent successfully"
                            ];
                            if($verification->newOTPReq($username,$pending_phone)) {
                                $REST->response(statusCode:200,flag:"1",message: $resp);  
                            }
                            

                        }else {
                            //account already activated
                            throw new \clientException(ErrorCode:"1307");
                        }
                        

              
                    }else {
                        throw new \clientException(ErrorCode:"2100",code:400);

                    }

                    break;
   
                case in_array("newtoken",$requestParamsKeys):

                    if($REST->getRequestMethod() == "POST") {

                        //requesting for new verification token to activate the email
                        
                        //check the existing status and load the respected params to varible .NOTE: load what need
                        $existingData = $verification->existingData($username);

                        $status = $existingData["status"];
                        $verified_E = $existingData["verified_E"];
                        
                        $pending_email = $existingData["pending_email"];
                        echo $pending_email."not workong";
                        if($verified_E == 0) {
                            //proceed further
                            $resp = [
                                "flag"=> "1",
                                "message"=>"token sent to email"
                            ];
                            if($verification->newTokenReq($username,$pending_email)) {
                                $REST->response(statusCode:200,flag:"1",message: $resp);  
                            }
                            
                        }else { 
                            throw new \clientException("1206");
                        }

                    }else {
                        throw new \clientException(ErrorCode:"2100",code:400);

                    }
                    break;
                default:
                    throw new \serverException(ErrorCode:"2000");
                    break;
            }
            //check if the user session is valid or expired
            $sessionManager = new \library\sessionManager;
   
        }else {

            throw new \clientException(ErrorCode:"1907");
        }


    }catch(\PDOException $e) { //This is more subclassification as of now it is okay
        logg(file:"db_err",exception_: $e);
        throw new \databaseException(ErrorCode:"1803");
    }
    catch (\Throwable $e) {
        if($e instanceof \clientException) {
            logg(file:"client_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }
        elseif ($e instanceof \databaseException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }elseif ($e instanceof \cacheException) {
            logg(file:"redis_err",exception_: $e);
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