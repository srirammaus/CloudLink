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