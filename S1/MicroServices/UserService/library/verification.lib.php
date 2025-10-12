<?php
namespace library;

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/sessionManager.lib.php";
include_once __DIR__."/../utils/cache.php";
include_once __DIR__."/../Events/EventProducer.svc.php";
use \utils\timeManager;
use function \library\logg;
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

        public $OTP_PREFIX = "users:verification:OTP"; // + username
        public $TOKEN_PREFIX = "users:verification:EMAILTOKEN";// +usernma
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
        try{


            if($this->email != NULL && $this->email_token != NULL ) {
                $ep = new \Events\EventProducer;
                $ep->createProducer();
                $ep->setTopic("user-events");
                $value = [
                    "email" => $this->email,
                    "email_token" => $this->email_token,
                ];
                $value = json_encode($value);
                $ep->setEvent(0,0,$value,"send-email-verification");
                $ep->emitEvent();
            }

        }catch (\RdKafka\KafkaErrorException $e) {
            logg(file:"kafka_err",message: $e);
            //proceed further no need to throw other exception
        }catch(\Throwable $e) {
            logg(file:"kafka_err",message: $e);

        }

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
        /**
         * get existing data from the redis
         */
        $this->username = $username;
        $query = "SELECT `status`,verified_E,
        verified_M,email,phone,
        email_token,phone_otp,
        pending_email,
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
        try {

            //assing vars
            $this->username = $username;
            $this->phoneOTP();

            //intialzie the cache
            $cache = new \utils\cachelib;
        
            //use stringss
            $key = $username;
            $items = [$this->phone_otp_new];
            $expiry = 300;
            
            //check for existence
            if(!$cache->isCached($key,$this->OTP_PREFIX)){

                $cached = $cache->setStringCache($key,$items,$expiry,$this->OTP_PREFIX); // this expiry not used
                if($cached) {
                    $cache->setExpiry($key,$expiry,$this->OTP_PREFIX);
                    return true;
                }else {
                    throw new \serverException(ErrorCode:"2000");
                }

            }else {
                throw new \clientException(ErrorCode:"1908");
            }
        }catch (\cacheException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \serverException(ErrorCode:"2000");
            
        }


    
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
        try {
 
            $this->username = $username;
            $this->phone_otp = $phone_otp;

            $cache =  new \utils\cachelib;
            $key = $this->username;
        
            $cached = $cache->getStringCache($key,$this->OTP_PREFIX); //return string

            if(!empty($cached)){
                if($this->phone_otp == $cached){
                    return true;
                }else {
                    throw new \clientException(ErrorCode:"1308");
                }

            }else {
                throw new \clientException(ErrorCode:"2500");
            }
        }catch (\cacheException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \serverException(ErrorCode:"2000");
            
        }
        
    }
    /**
     * update the existing token
     * and send the token to email
     */
    public function newTokenReq($username,$email) {
        /**
         * set cache 
         */

        try {

            //assing vars
            $this->username = $username;
            $this->emailToken();

            //intialzie the cache
            $cache = new \utils\cachelib;
        
            $key = $username;
            $items = [$this->email_token_new];
            $expiry = 300;

            //check for existence 
            $isExist = $cache->isCached($key,$this->TOKEN_PREFIX);
            if(!$isExist) {
                //use strings
                $cached = $cache->setStringCache($key,$items,$expiry,$this->TOKEN_PREFIX); // this expiry not used
                if($cached) {
                    $cache->setExpiry($key,$expiry,$this->TOKEN_PREFIX);
                    $this->email = $email ?? NULL;
                    $this->email_token = $this->email_token_new ??NULL;
                    $this->sendToNotificationService();
                    return true;

                }else {
                    //some unknown error occured
                    throw new \serverException(ErrorCode:"2000");
                }
            }else {
                throw new \clientException(ErrorCode:"1908",code:400);
            }
        }catch (\cacheException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \serverException(ErrorCode:"2000");
            
        }
   
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
        try {

            $this->username = $username;
            $this->email_token = $email_token;

            
            $cache =  new \utils\cachelib;
            $key = $this->username;

            $cached = $cache->getStringCache($key,$this->TOKEN_PREFIX); //return string
            echo $cached;
            if(!empty($cached)){
                if($this->email_token == $cached){
                    return true;
                }else {
                    throw new \clientException(ErrorCode:"1207");
                }

            }else {
                throw new \clientException(ErrorCode:"1705");
            }
        
        }catch (\cacheException $e) {
            //take log and throw error again
            logg(file:"redis_err",exception_: $e);
            throw new \serverException(ErrorCode:"2000");
            
        }
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
    public function updateStatus ($username,$status,$verified_M,$verified_E,$pending_email=NULL,$pending_phone=NULL) {
        // This query updates the status and verified_M , 
        // why verified_M becuase whatever the action the verified_M need to be 1 then only the staus is active
        // So this query satisfy the verifyOTP needs
        if($pending_email != NULL ) {
            $query = "UPDATE users SET `status`=:status,verified_M=:verified_M,verified_E=:verified_E,email=:email WHERE 
                 username=:username ";
        }
        if($pending_phone !=NULL) {
            $query = "UPDATE users SET `status`=:status,verified_M=:verified_M,verified_E=:verified_E,phone=:phone WHERE 
                 username=:username ";
        }
        $prep = $this->getConn()->prepare($query);
        $prep->bindParam(":username",$username);
        $prep->bindParam(":status",$status);
        $prep->bindParam(":verified_M",$verified_M);
        $prep->bindParam(":verified_E",$verified_E);
        if($pending_email != NULL) {
            $prep->bindParam(":email",$pending_email);

        }
        if($pending_phone !=NULL) {
            $prep->bindParam(":phone",$phone);

        }

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


// $v = new verification;
// $existingData = $v->existingData("admin");

// //this should be from actually db
// $status = $existingData["status"];
// $verified_M = $existingData["verified_M"];
// $phone = $existingData["phone"];
// $email = $existingData["email"];
// try{
//     $v->newOTPReq("admin",$phone);
//     // $v->verifyOTP("admin",770611);
//     // $v->newTokenReq("admin",$email);
//     // $v->verifyToken("admin","697456d4b7ebf16c16ceff0dd7c973b3");


// }catch(\clientException $e) {
//     echo $e->getCustomMessage();
// }
// catch(\Throwable $e) {
//     echo $e->getMessage();
// }


?>