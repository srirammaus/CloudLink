<?php
namespace library;

include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once "sessionManager.lib.php";

/**
* "username": "shriram@example.com",
* "password": "mypassword123",
* "captcha_token": "03AGdBq24...",
* "remember_me": true,
* "timezone": "Asia/Kolkata",
* "client_id": "web_portal",
* "mfa_code": "452918"
*/
class signin {
        public $username ;
        public $password ;
        public $remember_me;
        public $client_id; //web or mobile 
        public $captcha;

        public $userid;
        public $storedPwd;
        public $timezone;
    public function __construct () {

    }   
    public function getConn() {
        $conn =  new \db\db_conn();
        return $conn->conn();
    }
    public function cmpPwd ($storedPwd) {
        $SALT = "lkjfsdfksdnfnsdkfnsdkfslkdnfklsdfldsnfndslfnsdklndf";
        $combinedPwd = $this->password.$SALT;
        return password_verify($combinedPwd,$storedPwd);
    }
    /**
     * check whether the parameters are valid
     * sanitize username before login
     * we gonna sanitize username with genLib($username,$email) -  for email just put temp@gmail.com
     */
    public function sanitize(array $requestParams) {
        $genLib = new \library\genLib;
        // $arr = ["username","password","remember_me","timezone","captcha_token"];
        $validparams = ["username","password","timezone"];
        if($genLib->isValidParams($validparams,$requestParams)){
            if($genLib->isValidUsername(username: $requestParams["username"],email:"xxxx@gmail.com")){
                if($genLib->isValidTimeZone($requestParams["timezone"])){
                    return true;
                }else {
                    throw new \clientException(ErrorCode:"2101");
                }
            }
        }else {
            throw new \clientException(ErrorCode:"1904");
        }

    }
 
    public function login (array $requestParams) {
        //create session Manager 
        $sessionManager = new sessionManager();
        $loggedIn = $sessionManager->isAuthenticated();
        // var_dump($loggedIn);
        if($loggedIn) {
            //redirecting to dashboard
            $response_data = [
                "flag" => "1",
                "username"=> $loggedIn["username"],
                "message"=> "login sucessfull ,refresh",
            ];
            return $response_data;
        
        }else {
            $this->sanitize($requestParams);
            // echo  phpversion()."\n";
            $this->username = $requestParams["username"];
            $this->password = $requestParams["password"];
            $this->timezone = $requestParams["timezone"];
            $this->remember_me = $requestParams["remember_me"] =="true"? true : false;
            $query = "SELECT * FROM users WHERE
                    username= '$this->username'";

            $result = $this->getConn()->query($query);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $result->fetch()?:array();
            if(count($result) > 0) {
                $this->storedPwd = $result["password"];
                $this->userid = $result["userid"];
                $cmp = $this->cmpPwd($this->storedPwd);
                // echo $cmp."\n";
                if($cmp) {
                    //check whether the account is active or not

                    $active_status = $result["status"];
                    $verified_E = $result["verified_E"];
                    $verified_M = $result["verified_M"];
                      /**
                         * It defines whethere the account is active or inactive or deleted or blocked (reported for any activity)
                        * active - 1
                        * inactive - 0
                        * deactived- -1
                        * deleted - -2
                        * blocked or reported - -3
                         */
                    if($active_status == 1) {

                        //create the session and store in db
                        $sessionManager->createSession($this->userid,$this->username,$this->timezone,$this->remember_me);

                        //set sessions 
                        $sessionManager->setSession();

                        //send response
                        $response_data = [
                            "flag" => "1",
                            "username"=>$this->username,
                            // "userid"=> $result["userid"],
                            "message"=> "login sucessfull main",
                        ];
                        return $response_data;
                    
                    }
                    //not activated , phone number not verified
                    else if($active_status === 0 || $active_status === "0") {
                        //send response
                       throw new \verificationException(ErrorCode:"1305");
                    
                    }
                    else if($active_status == -1) {
                       throw new \verificationException(ErrorCode:"2301");

                    }
                    else if($active_status == -2) { // account recover possible only with company permission mail
                       throw new \verificationException(ErrorCode:"2302");

                    }else if($active_status == -3){ // -3  // account recover possible only with company permission mail
                       throw new \verificationException(ErrorCode:"2303");

                    }else {
                        throw new \serverException(ErrorCode:"2000");
                    }
                }else {
                    throw new \clientException(ErrorCode:"1100");
                }
            }else {
                throw new \clientException(ErrorCode:"1000");
            }
        }

    }

    public function rememberMe() {

    }

}

?>