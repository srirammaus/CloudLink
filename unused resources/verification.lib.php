<?php
namespace library;

include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once "sessionManager.lib.php";

use \utils\timeManager;
/**
 * while you are using this library in any api that place might have the try catch, in that catch add the pdoexception
 * The phone number is compulsory verified before ACTIVE, email is secondary // for temporray use email use to verify account
 * verified_E - email
 * verified_M - mobile or phone
 * For -3 and -2 the account recovery only possible if the company manually verified the mail
 * active - 1
 * inactive - 0
 * deactived- -1
 * deleted - -2
 * blocked or reported - -3 
*/
class verification {
        public $username;
        public $email;
        public $phone;

        public $status;
        public $verified_E;
        public $verified_M;
        public $email_token_expiry;
        public $phone_otp_expiry;

        public $phone_otp_new;
        public $email_token_new;
    public function __construct () {

    }
    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    /**
     * generate token
     * Sending the Email to Event broker (Service name: Notification Service)
     */
    public function emailToken() {
        $byteLen = 16;
        $this->email_token_new =  \library\genLib::generateToken($byteLen);
        $this->email_token_expiry = timeManager::utcNowSeconds() + 180; //180-3min
        

    }
    public function phoneOTP () {
        $this->phone_otp_new=  \library\genLib::generateOTP();
        $this->phone_otp_expiry = timeManager::utcNowSeconds() + 180; //180-3min

    }
    /**
     * This uses the serivce/EventManager.php to send the messages to notification service
     */
    public function sendToNotificationService() {

    }
    /**
     * check existing status before verifiying 
     * because what if the user account is blocked or deleted
     * This function primarily used to check the existing staus before 
     * intiating the below
     * 1. reverfication request 
     * 2. activating the account by checking phone otp 
     * 3. used the verify the email ,
     * Action should only happen if existing status is 0 or -1 , not for -2 and -3
     * 
     * query action happen by username
     */
    public function existingData($username) :array {
        $this->username = $username;
        $query = "SELECT `status`,verified_E,
        verified_M,email,phone,
        email_token,phone_otp,
        email_token_expiry,phone_otp_expiry FROM `users` WHERE username=:username";

        $prep = $this->getConn()->prepare($query);
        $prep->bindParam(":username",$username);
        $res = $prep->execute();
        if($res){ //if code executes
            $prep->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prep->fetch()?:array();
            if(count($result) > 0) {
                return $result;
                
            }
            return [];
        }
        return [];
        


                
    }
    public function isNotExpired($expiry) {
        echo $expiry > timeManager::utcNowSeconds()."\n";
        return $expiry > timeManager::utcNowSeconds();
    }
    /**
     * update the existing OTP
     * send to mobile phone
     */
    public function newOTPReq($username,$phone) {
        /**
         * set cache 
         */
        $this->username = $username;
        $this->phoneOTP();
        $query = "UPDATE `users` SET phone_otp=:phone_otp,phone_otp_expiry=:phone_otp_expiry WHERE username=:username";
        $prep = $this->getConn()->prepare($query);

        $prep->bindParam(":phone_otp",$this->phone_otp_new);
        $prep->bindParam(":phone_otp_expiry",$this->phone_otp_expiry);
        $prep->bindParam(":username",$this->username);

        $res = $prep->execute();

        if($res) {
            //success
            return true;

            //send the OTP respectively through the phone service

        }
        return false;
    
    }
    /**
     * OTP send to user
     */
    public function sendOTP($phone_otp){

    }
    /**
     * verify the OTP 
     */

    public function verifyOTP ($username,$phone_otp) {
        /**
         * get cache 
         */
        $this->username = $username;
        $this->phone_otp = $phone_otp;

        $query = "SELECT userid FROM users WHERE 
        username=:username AND phone_otp=:phone_otp ";

        $prep = $this->getConn()->prepare($query);
        $prep->bindParam(":username",$username);
        $prep->bindParam(":phone_otp",$phone_otp);

        $res = $prep->execute();
        if($res){ //if code executes
            $prep->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prep->fetch()?:array();
            if(count($result) > 0) {
                return true;
            }
            return false;
        }
        return false;
        
    }
    /**
     * update the existing token
     * and send the token to email
     */
    public function newTokenReq($username,$email) {
                /**
         * set cache 
         */
        $this->username = $username;
        $this->emailToken();
        $query = "UPDATE `users` SET email_token=:email_token,email_token_expiry=:email_token_expiry WHERE username=:username";
        $prep = $this->getConn()->prepare($query);

        $prep->bindParam(":email_token",$this->email_token_new);
        $prep->bindParam(":email_token_expiry",$this->email_token_expiry);
        $prep->bindParam(":username",$this->username);

        $res = $prep->execute();

        if($res) {
            //success 
            echo $this->email_token_new."\n";
            echo $this->email_token_expiry;
            return true; 

            //send the email respectively through the email service
        }
        return false;
    }
    /**
     * mail send to user
     */
    public function sendToken($email_token){

    }
    /**
     * verifiy the token
     */

    public function verifyToken($username,$email_token) : bool{

        /**
         * set cache 
         */
        $this->username = $username;
        $this->email_token = $email_token;

        $query = "SELECT userid FROM users WHERE 
        username=:username AND email_token=:email_token ";

        $prep = $this->getConn()->prepare($query);
        $prep->bindParam(":username",$username);
        $prep->bindParam(":email_token",$email_token);

        $res = $prep->execute();
        if($res){ //if code executes
            $prep->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prep->fetch()?:array();
            if(count($result) > 0) {
                return true;
            }
            return false;
        }
        return false;
        
    }

    /**
     * active - 1
     * inactive - 0
     * deactived- -1
     * deleted - -2
     * blocked or reported - -3 
     * This is used to update the status, verified_E,verified_M
     * Actions: 
     * if deletion the status  changed to -2 , no need of interaction in v_E andd v_M
     * if deactivation the status changed to -1, no need of interaction in v_E andd v_M
     * if blocked the status changed to -3 ,, no need of interaction in v_E andd v_M
     * if activated the status changed to 1, compulsorily the v_M should be 1
     * if the number changed , there is only need of interfering with logic and already th
    */
    public function updateStatus ($username,$status,$verified_M,$verified_E) {
        // This query updates the status and verified_M , 
        // why verified_M becuase whatever the action the verified_M need to be 1 then only the staus is active
        // So this query satisfy the verifyOTP needs
        $query = "UPDATE users SET `status`=:status,verified_M=:verified_M,verified_E=:verified_E WHERE 
        username=:username ";
        $prep = $this->getConn()->prepare($query);
        $prep->bindParam(":username",$username);
        $prep->bindParam(":status",$status);
        $prep->bindParam(":verified_M",$verified_M);
        $prep->bindParam(":verified_E",$verified_E);

        $res = $prep->execute();
        if($res) {
            $result = $prep->rowCount();
            echo $result."\n";
            if($result > 0) {
                return true;
            }
            return false;
        }
        return false;

    }

//     $this->status = $result["status"];
// $this->verified_E = $result["verified_E"];
// $this->verified_M = $result["verified_M"];
// $this->email_token_expiry["email_token_expiry"];
// $this->phone_otp_expiry["phone_otp_expiry"];

}


?>