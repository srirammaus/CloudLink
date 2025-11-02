<?php
namespace library;


/**
 * Profile updation
 *  Magic Method :__construct(), __destruct(), __call(), __callStatic(), __get(), __set(), __isset(), __unset(), __sleep(), __wakeup(), __serialize(), __unserialize(), __toString(), __invoke(), __set_state(), __clone(), and __debugInfo().
 * 
 * update profile duties
 * params can be update are follwing : [name,username - 1yr once,email,secondary email,phone number,bio]
 */


                // "status": "1", -- overall activation
                // "verified_E": "1",  -- email verificaiion
                // "verified_M": "1",   -- mobileverficaiot 
                // "email_token": "889ff0bd4ce56f5a470c56ae13092218", -- email token
                // "phone_otp": "472800", -- phone otp
                // "email_token_expiry": "1757070561", -- epxiration
                // "phone_otp_expiry": "1757128246"

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/sessionManager.lib.php";
include_once __DIR__."/../Events/EventProducer.svc.php";

class updateProfile {
    public $new_name;
    public $new_username;
    public $new_email;
    public $new_secondary_email;
    public $new_phone_number;
    public $new_bio;
    public $email_token;
    public $otp;

    public $username;
    public $send_email = FALSE;
    public $send_otp = FALSE;

    public $update_data= [];
    public function __construct ($requestParams) {
        $this->new_name = $requestParams["new_name"] ?? NULL;
        $this->new_username = $requestParams["new_username"] ?? NULL;
        $this->new_email = $requestParams["new_email"] ?? NULL;
        $this->new_secondary_email = $requestParams["new_secondary_email"] ?? NULL;
        $this->new_phone_number = $requestParams["new_phone_number"] ?? NULL;
        $this->new_bio = $requestParams["new_bio"] ?? NULL;
        $this->username = $_COOKIE["username"] ?? NULL;

        $this->update_data = [];

    }

    public function getConn() {
        $conn =  new \db\db_conn();
        return $conn->conn();
    }
    /**
     * 
     */
    public function sanitize () {
        $genLib = new genLib();
        $err = NULL;
        //vulgard word and len
        if($this->new_name != NULL) {
            if(!$genLib->vulgarWordChecker($this->new_name) && strlen($this->new_name) < 15 ){
                $this->update_data["name"] = $this->new_name;

            }else {
                $err = "1806";
            }
        }
        //vulger and len and check for uniqnness and isvalid username
        if($this->new_username != NULL) {
            if($genLib->isValidUsername($this->new_username,"temp@gmail.com")) {
                $this->update_data["username"] = $this->new_username;

            }else {
                $err = "1000";
            }

        }
        
        //vulger and len andd isValidemail and check for unqiness
        if($this->new_email != NULL) {
            if($genLib->isValidUsername("temp",$this->new_email)) {
                $this->update_data["pending_email"] = $this->new_email;
                // $this->email_token = $genLib->generateToken();
                // $this->update_data["email_token"] = $this->email_token;
                $this->send_email = true;
            }else {
                $err = "1200";
            }

            
        }
        //vulger and len andd isValidemail and check for unqiness
        if($this->new_secondary_email != NULL) {
            if($genLib->isValidUsername("temp",$this->new_secondary_email)) {
                    
                $this->update_data["secondary_email"] = $this->new_secondary_email;
                
            }else {
                $err = "1200";
            }
            
        }
        //isvalid phone
        if($this->new_phone_number != NULL) {
            if($genLib->isValidPhone($this->new_phone_number)) {
                $this->update_data["pending_phone"] = $this->new_phone_number;
                $this->otp = $genLib->generateOTP();
                $this->update_data["phone_otp"] = $this->otp;
                $this->send_otp = true;

            }else {
                $err = "1300";
            }
            
        }
        //vulgor and len
        if($this->new_bio != NULL) {
            if(!$genLib->vulgarWordChecker($this->name) && strlen($this->new_name) < 50 ){
                $this->update_data["bio"] = $this->new_bio;
            }else {
                $err = "1806";
            }
        }
        if($err !=NULL) {
            throw new clienException(ErrorCode:$err);
        }


    }
    public function updateProfile () {
        $session_token = $_COOKIE["sessionToken"] ??NULL;
        $session_id = $_COOKIE["sessionID"]?? NULL;
  

        $this->sanitize();
        $queryString = "";
        $count =0;
        if(!empty($this->update_data)) {
            $c = count($this->update_data);
            foreach ($this->update_data as $key => $value) {
                # code...
                if($count == ($c - 1)) {
                    $queryString .= " u.$key='$value' ";
                }else {
                    $queryString .= " u.$key='$value',";

                }

                $count++;
            }   
        }
        $query = "UPDATE users u JOIN sessions s ON s.session_token=:session_token AND s.username=:username  SET".$queryString." WHERE u.username=:username";
        // echo $query;
        $conn = $this->getConn();
        $stmt = $conn->prepare($query);

        $stmt->bindParam(":username",$this->username);
        $stmt->bindParam(":session_token",$session_token);

        $res = $stmt->execute();
        if($res) {
            $updatedRow = $stmt->rowCount();
            if($updatedRow > 0 ) {


            // $this->sendToNotificationService();     //uncomment this later
            return TRUE;


            }else {
                throw new \clientException(ErrorCode:"1805");
            }
        }else {
            throw new \databaseException(ErrorCode:"1803");
        }
        
    }
    /**
     * send the notfication to the cloudLink application
     * send the email token
     * send the phone otp
     */
    public function sendToNotificationService() {
        try{

            $ep = new \Events\EventProducer;
            $ep->createProducer();
            $ep->setTopic("user-events");
            if(!empty($this->new_email) && !empty($this->email_token) ) {
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
    public function __destruct () {

    }
}
?>