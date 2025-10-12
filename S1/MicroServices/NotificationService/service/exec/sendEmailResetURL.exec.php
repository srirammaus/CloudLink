<?php
namespace service\exec;
/**
 * This file is used to send the rest url through email or phone
 * 
 */

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
include_once __DIR__."/../library/mailer.lib.php";  // send resetURL through email
include_once __DIR__."/../library/firebaseSMS.lib.php"; //send resetURL through OTP

include_once __DIR__."/../library/notifyCloudLinkApp.lib.php"; //update the notification about the password changed  after user login 


class sendResetURL {
    public $content;
    public $reciever_email;
    public $phone;

    public $notification_content;
    public function _construct() {

    }

    public function getTemplate () {

    }
    public function sendPhone() {

    }
    public function sendEmail ($reciever_email,$content) {
        try {
            $mail =  new \service\library\mailer;
            $mail->sendMail();
        }catch (\Throwable $e) {
            if($e instanceof \clientException) {
                logg(file:"client_err",exception_: $e);
            }
            elseif ($e instanceof \databaseException) {
                logg(file:"db_err",exception_: $e);

            }else if($e instanceof \serverException) {
                logg(file:"server_err",exception_: $e);

            }else if($e instanceof \verificationException) {
                logg(file:"client_err",exception_: $e);

            }
            else { 
                logg(file:"server_err", exception_:$e);
                $REST->response($e->getCode(),"2003","Internal Server Error"); //here message should be internal error
            }

        }

        

    }
    /***
     * storing the new data by using AppnotificationManager 
     * push the 
     */
    public function updateNotification() {
        $this->notification_content = [
            "img"=>"imageURL",
            "message"=> "password changed successfully"
        ];
    }

}
?>