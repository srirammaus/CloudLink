<?php 
/**
 * API need for this are
 *  forgotPwd.api.php
 *  resetPWd.api.php
 * 
 * for password changed inside the dashboard are called updatePwd.api.php that not comes under this library
 * This library is used change password without login
 * if you forgot your pwd , you click the forgot option
 * then you will redirected to page that page asks you 
 * to type the username or phone or email
 * if username or phone number is entered , the otp is send to the particular
 * phone and vaalidate through it and if the email typed the token is send to 
 * the email id . check whether the phone number and email is already verfiied or not
 * 
 *
 */
namespace library;

include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once "sessionManager.lib.php";
include_once "updatePwd.lib.php";
use \utils\timeManager;

class forgotPwd {
    public $username;
    public $email;

    public $URL_token_new;
    public $expiry;
    public $key;
    public $val;

    
    public function __construct() {

    }
    public function getConn() {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    /**
     * generate token
     * Sending the Email to Event broker (Service name: Notification Service)
     */
    public function URLToken() {
        $byteLen = 16;
        $this->URL_token_new =  \library\genLib::generateToken($byteLen);
        $this->expiry = timeManager::utcNowSeconds() + (3600 * 5) ; // 5hrs

    }

    public function sendToNotificationService () {

    }
    /**
     * generating the Token send to the email or phone
     * merge the token with reset URL
     * storing it in redis with short or 5 minttl
     */
    public function sendResetURL ($key = NULL,$val = NULL,$URL_token_new = NULL,$username = NULL) {
                /**
         * get cache 
         */
        $key = $key ?? $this->key;
        $val = $val ?? $this->val;
        $URL_token_new = $URL_token_new??$this->URL_token_new;
        $username = $username ?? $this->username;
        
        $config = include_once __DIR__."/../config/URLs.php";

        //use always http build query or url encode
        $resetParams = [
            "username" => $this->username,
            "token"=>$this->URL_token_new,
        ];

        //actaully this URL should be frontend-app or web page showing URL should
        // be sent not resetPwd_API directly 
        // first the forgot pwd sent the web page url(frontend.app/resetpwd?token=fsdfdsf&username=admin) like this
        //This URL is temporrary
        $resetURL = $config["resetPwd_API"]."?".http_build_query($resetParams);

        if($key == "email" ) {   
            //send the rest URL + token in email
            $email = $this->val;

            //if true send this as resposnse
            return $resp = [
                "flag" =>"1",
                "temp-URL" => $resetURL,
                "messsage" => "reset URL sent your mail",
            ];
        }else {
            //send the reset url + token in SMS
            $phone = $this->val;

            //if true send this as resposnse
            return $resp = [
                "flag" =>"1",
                "temp-URL" => $resetURL,

                "messsage" => "reset URL sent your number",
            ];
        }


    }


    /**
     * for changing the for password he forgot the account must be active measn status = 1
     * The duty of this function is , user will enter either username or email or phone 
     * check the existing status , verified_M , verified_E.
     * if status 0 , redirect (redirection happen via frontend )him to verfiy the mobiile number and activate the account (nowitsel he can also use email)
     * if status 1 , then take him further process
     */
    public function forgotPassword($username = NULL,$email=NULL,$phone=NULL) {

                /**
         * set cache 
         */
        $condition = []; //we gonna imploade this 
        $params = [];
        $key;
        if (!empty($username)) {
            $condition[] = "username=:username";
            $params[":username"] = $username;
            $key = "phone";  // for username we using deafult phone 
        }
        if (!empty($email)) {
            $condition[] = "email=:email";
            $params[":email"] = $email;
            $key = "email";
        }
        if (!empty($phone)) {
            $condition[] = "phone=:phone";
            $params[":phone"] = $phone;
            $key = "phone";
        }
        echo implode(" OR ",$condition);
        $query = "SELECT username,email,phone,verified_E,verified_M,`status` 
        FROM users WHERE ".implode(" OR ",$condition);

        $prep = $this->getConn()->prepare($query);
        // $prep->bindParam(); //array_keys($params)[0],array_values($params)[0]
        $res = $prep->execute($params);

        if($res) {
            $prep->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prep->fetch()?:array();
            if(count($result) > 0) {
                $status = $result["status"];
                $verified_E = $result["verified_E"];
                $verified_M = $result["verified_M"];
                $username = $result["username"];
                if($status == 1) { //status 1 shows that mobile number is activate, but anyhow a hacker came insdie , so we gonna made a another check of verified_M if it was mobile number reset pwd

                    //further process
                    if($key == "email" && $verified_E == 1) {
                        $val = $result["email"];
                        //furhter process
                        return $this->addResets($key,$val,$username); // bool
                    }else  if($key == "phone" && $verified_M == 1) {
                        $val = $result["phone"];
                        //further process
                        return $this->addResets($key,$val,$username); // bool
                    }else if($key == "email" && $verified_E == 0){ 
                        throw new \clientException(ErrorCode:"1208");
                    }else {  
                        //mobile verification to activate the accounnt 
                        throw new \clientException(ErrorCode:"1305");

                    }
                }else {
                    //mobile verification to activate the accounnt
                    throw new \clientException(ErrorCode:"1305");

                }
          
            }else {
                throw new \clientException(ErrorCode:"1607");

            }
        }else {
                throw new \databaseException(ErrorCode:"1803");

        }
    }
    public function alreadyChanged($username) {

    }
    /**
     * add pwd reset table
     * This function depends on udpatePwd.lib.php
     * later
     */
    public function addResets ($key =NULL,$val =NULL,$username = NULL):bool {
        /**
         * username  - username
         * token - the generated token
         * expiry - the expiry time of token . in UTC seconds
         * status - 0 is not used , 1 means token used , -1 expired -( this work also shared by event scheduler )
         */
        $this->key = $key;
        $this->val = $val;
        $this->username = $username;
        
        $reset_type = "URL";
        echo $this->key."\n";
        echo $this->val."\n";

        $this->URLToken();
        //dont confuse $this->key with the ON DUPLICATE KEY ,that dupliacte key is the key i set in the db by using manual query
        $query = "INSERT INTO reset_table 
        (username,".$this->key.",token,expiry,status,reset_type)  VALUES (
        :username,:".$this->key.",:token,:expiry,:status,:reset_type) 
        
        ON DUPLICATE KEY UPDATE 
        ".$this->key." = VALUES(".$this->key."),
        token = VALUES(token),
        expiry = VALUES(expiry),
        status = VALUES(status),
        reset_type=VALUES(reset_type)
        ";
        
        $prep = $this->getConn()->prepare($query);

        $token_status = 0; //default - not used
        $prep->bindParam(":username",$this->username);
        $prep->bindParam(":token",$this->URL_token_new);
        $prep->bindParam(":expiry",$this->expiry);
        $prep->bindParam(":status",$token_status);
        $prep->bindParam(":".$this->key,$val);
        $prep->bindParam(":reset_type",$reset_type);

        $res = $prep->execute();
        if($res) {
            return true;
        }else {
            throw new \databaseException(ErrorCode:"1803");
        }

        
    }
    public function isValidUser () { // check if the user is existed or not
        $this->username = $username;
        $this->email = $email;
        
        $genLib = new \genLib();
        $genLib->isExistingUser();
    }

}

?>