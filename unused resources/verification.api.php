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

                        $status = $existingData["status"]; // For temporary i'm activating the active status through email
                        $verified_E = $existingData["verified_E"];
                        $verified_M = $existingData["verified_M"];

                        $email = $existingData["email"];
                        $email_token_expiry = $existingData["email_token_expiry"];

                        $email_token_db = $existingData["email_token"];
                        $email_token = $requestParams["email_token"];

                        if($verified_E == 0) { 
                            if($verification->isNotExpired($email_token_expiry)){
                                
                                //check both the tokens are same
                                if($email_token_db === $email_token)  {

                                    //update the active status temporary by using email
                                    $new_status = 1;  //This has to be as it was . $status
                                    $new_verified_E = 1;
                                    $new_verified_M = $verified_M; // This is should be what it was, so i put like this, because it is mobile number verification, this func doesn't kno whether that the mobile number verified or not , so we put what that was
                                    
                                    //This should be consider as activate because ,as of now we using mail to activate
                                    $verifyMail = $verification->updateStatus($username,$new_status,$new_verified_M,$new_verified_E);
                                
                                    if($verifyMail) {   
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
                                    throw new \clientException(ErrorCode:"1207");
                                }
                            }else {
                                throw new  \clientException(ErrorCode:"1205");
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

                        $phone = $existingData["phone"];
                        $phone_otp_expiry = $existingData["phone_otp_expiry"];

                        $phone_otp_db = $existingData["phone_otp"];
                        $phone_otp = $requestParams["phone_otp"];

                        if($verified_M == 0) { 
                            if($verification->isNotExpired($phone_otp_expiry)){
                                
                                //check both the OTP are same
                                if($phone_otp_db === $phone_otp)  {

                                    $new_status = 1;
                                    $new_verified_M = 1;
                                    $new_verified_E = $verified_E; // This is should be what it was, so i put like this, because it is email verification, this func doesn't kno whether that the email verified or not , so we put what that was
                                    
                                    //It activates the user account
                                    $verifyPhone = $verification->updateStatus($username,$new_status,$new_verified_M,$new_verified_E);
                                
                                    if($verifyPhone) {   
                                    //redirection  and redirection URL must contain status = success, which should again cross check whether the verified_E is 1 and temporly check status is 1
                                        $resp = [
                                            "flag"=>"1",
                                            "Message"=>"sucessfully activated",
                                        ];
                                        $REST->response(statusCode:200,flag:"1",message: $resp);  
                                    }else {
                                        echo "Here comes";
                                        throw new \databaseException(ErrorCode:"1803");
                                    }
                                }else {
                                    throw new \clientException(ErrorCode:"1308");
                                }
                            }else {
                                throw new  \clientException(ErrorCode:"1306");
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

                        $phone = $existingData["phone"];
                        $phone_otp_expiry = $existingData["phone_otp_expiry"];

                        //check the old token is valid or not , if not expired then send hime that
                        if($verification->isNotExpired($phone_otp_expiry)) {
                            $resp = [
                                "flag"=> "1",
                                "Message"=>"OTP sent successfully..",
                            ];
                            // resend the mail only,  not upate the db
                            $REST->response(statusCode:200,flag:"1",message: $resp); 
                        }else {
                            //create ,store 
                            if($verification->newOTPReq($username,$phone)) {

                                //send the token through
                                //nowitself that work hasn't been done, so as of now send resposne

                                $resp = [
                                    "flag"=> "1",
                                    "Message"=>"OTP sent successfully",
                            
                                ];
                                $REST->response(statusCode:200,flag:"1",message: $resp);
                            }else {
                                throw new \databaseException(ErrorCode:"1803");
                            }
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

                        $email = $existingData["email"];
                        $email_token_expiry = $existingData["email_token_expiry"];

                        //check the old token is valid or not , if not expired then send hime that
                        if($verification->isNotExpired($email_token_expiry)) {
                            $resp = [
                                "flag"=> "1",
                                "Message"=>"Mail sent successfully..",
                            ];
                            // resend the mail only,  not upate the db
                            $REST->response(statusCode:200,flag:"1",message: $resp); 
                        }else {
                            //create ,store 
                            if($verification->newTokenReq($username,$email)) {

                                //send the token through
                                //nowitself that work hasn't been done, so as of now send resposne

                                $resp = [
                                    "flag"=> "1",
                                    "Message"=>"Mail sent successfully",
                            
                                ];
                                $REST->response(statusCode:200,flag:"1",message: $resp);
                            }else {
                                throw new databaseException(ErrorCode:"1803");
                            }
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

        }else if($e instanceof \serverException) {
            logg(file:"db_err",exception_: $e);
            $REST->response($e->getCode(),$e->getErrorCode(),$e->getCustomMessage());

        }else if($e instanceof \verificationException) {
            logg(file:"db_err",exception_: $e);
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