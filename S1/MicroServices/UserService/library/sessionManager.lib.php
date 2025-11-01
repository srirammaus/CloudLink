<?php 
namespace library;

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/sessionManager.lib.php";
include_once __DIR__."/../utils/cache.php";

use \utils\timeManager;
use function \library\logg;
class sessionManager {
    #learn cookie parameters before creating sessions
    public $sessionID;
    public $sessionToken;
    public $refreshToken;
    public $s_expiry_utc;
    public $r_expiry_utc;
    public $s_expiry;
    public $r_expiry;
    public $maxAge;
    public $timezone;
    public $s_status;
    public $r_status;
    public $username_cookie;

    public $SESSION_PREFIX = "users:sessions"; //+sessionid is the key
    
    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    public function __construct() {

    }
    public function cookieParameters() : array {
        return [
            "sessionID"=> $this->sessionID,
            "sessionToken" => $this->sessionToken,
            "username" => $this->username,
            // "timezone" =>$this->timezone,
        ];
    }
    /***
     * This the custom ID . so __CLD denotes the cloudLink
     */
    public function genSessionID() {
        $id = random_bytes(8);
        $id =  bin2hex($id);
        $id2 = rand(1000,99999);
        $this->sessionID = "__CLD".$id2.$id;
    }
    public function genSessionToken () {
        $token = random_bytes(12);
        $token = bin2hex($token);
        $this->sessionToken = $token;
    }
    public function genRefershToken() {
        $token = random_bytes(16);
        $token = bin2hex($token);
        $this->refreshToken = $token;
    }
    /**
     * Expiry is always a fall back and prone to errors
     * if maxAge and expiry set together maxAge takes precednce
     *  */    
    public function setExpiry ($timezone = "Asia/KolKata") {
        $timezone = $timezone ?? "Asia/Kolkata";

        $currentUTC = timeManager::utcNowSeconds();
        $sessionExpiryTimeStamp = $currentUTC +  (60 *1); // (3600 * 24) -1day
        $refreshExpiryTimeStamp = $currentUTC + (84600 * 30); // (84600 * 30) - 30days
        $this->s_expiry_utc = $sessionExpiryTimeStamp;
        $this->r_expiry_utc = $refreshExpiryTimeStamp;
        
        $sessionExpiryTimeStamp= timeManager::secondsTotimestamp($sessionExpiryTimeStamp);
        $refreshExpiryTimeStamp = timeManager::secondsTotimestamp($refreshExpiryTimeStamp);

        $sessionUserExpiryTimeStamp = timeManager::utcTolocal($sessionExpiryTimeStamp,$timezone);
        $refreshUserExpiryTimeStamp = timeManager::utcTolocal($refreshExpiryTimeStamp,$timezone);

        $this->s_expiry = $sessionUserExpiryTimeStamp;
        $this->r_expiry = $refreshUserExpiryTimeStamp;

        // echo $this->s_expiry."\n";
        // echo $this->s_expiry_utc."\n";

        // echo $this->r_expiry."refresh_Expiry\n";
        // echo $this->r_expiry_utc."\n";


        $this->maxAge = 3600 * 24;
    }
    /**
     * s_status - 1 or 0 , session token status
     * r_status - 1 or 0 , refresh toke satus
     * 
     * r_expiry -  refresh token expiry - timestamp string
     * s_expiry - session token expiry - timestamp string
     * 
     * r_expiry_utc - refresh token expiry -  utc seconds
     * s_expiry_utc - session token expiry - utc seconds
     */
    public function createSession ($userid,$username,$timezone,$remember_me = false) :bool {
        $this->userid = $userid;
        $this->username = $username;
        $this->timezone = $timezone;

        $this->s_status = 1;
        $this->r_status = 1;
  
        $this->genSessionID();
        $this->genSessionToken();
        $this->genRefershToken();
        $this->setExpiry($timezone); // includes maxage and expiry . 

        if(!$remember_me) {
            // echo "This happened/";
            $this->refreshToken = NULL;
            $this->r_status = NULL;
            $this->r_expiry_utc = NULL;
            $this->r_expiry = NULL;
        }

        try{

        
            $query = "INSERT INTO sessions 
            (sessionid,session_token,refresh_token,userid,username
            ,s_expiry,r_expiry,s_expiry_utc,r_expiry_utc,max_age,timezone,s_status,r_status) 
            VALUES 
            (:sessionid,:session_token,:refresh_token,:userid,:username
            ,:s_expiry,:r_expiry,:s_expiry_utc,:r_expiry_utc,:max_age,:timezone,:s_status,:r_status)";

            $prepared_stmt = $this->getConn()->prepare($query);

            $prepared_stmt->bindParam(":sessionid",$this->sessionID);
            $prepared_stmt->bindParam(":session_token",$this->sessionToken);
            $prepared_stmt->bindParam(":refresh_token",$this->refreshToken);

            $prepared_stmt->bindParam(":userid",$this->userid);
            $prepared_stmt->bindParam(":username",$this->username);
            $prepared_stmt->bindParam(":s_expiry",$this->s_expiry);
            $prepared_stmt->bindParam(":r_expiry",$this->r_expiry);
            $prepared_stmt->bindParam(":s_expiry_utc",$this->s_expiry_utc);
            $prepared_stmt->bindParam(":r_expiry_utc",$this->r_expiry_utc);

            $prepared_stmt->bindParam(":max_age",$this->maxAge);
            $prepared_stmt->bindParam(":timezone",$this->timezone);
            $prepared_stmt->bindParam(":s_status",$this->s_status);
            $prepared_stmt->bindParam(":r_status",$this->r_status);

            $success= $prepared_stmt->execute();
            if($success) {
                // return ["flag" => "1",
                //     "message" => "sucessfully inserted session, please check your mail and activate your account ",
                // ];
                $this->cacheSession($this->username,
                $this->sessionID,
                $this->sessionToken);

                return true;
            }
        }catch(\PDOException $e) { //This is more subclassification as of now it is okay
            logg(file:"db_err",exception_: $e);
            throw new \databaseException(ErrorCode:"1803");
        }
        
        return false;
    }
    public function cacheSession($username,$sessionid,$session_token,$expiry = 3600) { //default ttl is 3600
        try {
        
            $key = $sessionid;
            //intialize the cache library 
            $cache =  new \utils\cachelib;

            $values = [
                "username"=> $username,
                "session_token"=> $session_token,
            ];
            //keep the cache with 1 hrs , then it will expired  and rehydrate the redis when he again enters and there is no cahc
            $cached = $cache->setAllHashCache($key,$values,$expiry,$this->SESSION_PREFIX);

            //HERE REDIS EXCEPTION OCCURS NO PROBLEM YOU JUST TAKE LOG AND FURTHER PROCEED TO DB AND VALIDATE THE SESSION TOKEN
            
            if($cached) { //1
                $cache->setExpiry($key,$expiry,$this->SESSION_PREFIX);
                return true;
            }
        }catch(\Throwable $e) {
            logg(file:"redis_err",exception_: $e);
            //YOU CAN PROCEED FURTHER
        
        }
    }
    /**
     * Dont check for existence , directly check for key 
     * in session all places we using hashmap
     */
    public function isSessionCached($sessionid){
        try {
            $key = $sessionid;
            $cache =  new \utils\cachelib;
            
            $cached = $cache->getAllHashCache($key,$this->SESSION_PREFIX);

            return $cached ?? []; //arr        
        }catch(\Throwable $e){
            logg(file:"redis_err",exception_: $e);
            return [];
        }

    }
    /**
     * Dont confuse with OAuth ,this is for remember me
     * persistent cookie changes once the session token updated
     */
    public function refreshSession($result_sessionID,$result_username,$timezone = "Asia/Kolkata") {

        $this->s_status = 1;
        $this->r_status = 1;
        
        $this->genSessionToken();
        $this->genRefershToken();
        $this->setExpiry($timezone); // includes maxage and expiry    

        
        try{

        //$query = "UPDATE `sessions` SET s_status = :s_status , r_status = :r_status WHERE $sessionid = :sessionID";
        
            $query = "UPDATE  `sessions` SET  
            session_token=:session_token
            ,refresh_token=:refresh_token
            ,s_expiry=:s_expiry
            ,r_expiry=:r_expiry
            ,s_expiry_utc=:s_expiry_utc
            ,r_expiry_utc=:r_expiry_utc
            ,max_age=:max_age
            ,s_status=:s_status
            ,r_status=:r_status WHERE sessionid=:sessionid";


            $prepared_stmt = $this->getConn()->prepare($query);

            $prepared_stmt->bindParam(":sessionid",$result_sessionID);
            $prepared_stmt->bindParam(":session_token",$this->sessionToken);
            $prepared_stmt->bindParam(":refresh_token",$this->refreshToken);

            $prepared_stmt->bindParam(":s_expiry",$this->s_expiry);
            $prepared_stmt->bindParam(":r_expiry",$this->r_expiry);
            $prepared_stmt->bindParam(":s_expiry_utc",$this->s_expiry_utc);
            $prepared_stmt->bindParam(":r_expiry_utc",$this->r_expiry_utc);

            $prepared_stmt->bindParam(":max_age",$this->maxAge);
            $prepared_stmt->bindParam(":s_status",$this->s_status);
            $prepared_stmt->bindParam(":r_status",$this->r_status);

            
            $success= $prepared_stmt->execute();
            if($success) {
                // return ["flag" => "1",
                //     "message" => "sucessfully inserted session, please check your mail and activate your account ",
                // ];
                $this->username = $result_username;
                $this->cacheSession($result_username,
                    $this->sessionID,
                    $this->sessionToken);
                // echo "cahce session done";
                // return true;
                $this->setSession();
                $resp = [
                        "username"=>$result_username,
                    ];
                return $resp;
            }
        }catch(\PDOException $e) { //This is more subclassification as of now it is okay
            logg(file:"db_err",exception_: $e);
            throw new \databaseException(ErrorCode:"1803");
        }
        
        return false;
    }
    /**
     * if user changed password this function should happen everywhere
     */
    public function expireAllSession() {

    }
    /**
     * setting the sessions to cookies while sending resposne
     * setcookie(name, value, expire, path, domain, secure, httponly);
    */

    public function setSession() {
        foreach ($this->cookieParameters() as $key => $value) {
            $paramters = 
              [//php does not contain maxage property 
                    //if http only is true that is cant acesed by the document.cookie
                    //time() always belongs to UTC or GMT 
                    "expires" => time() + (3600 * 24),
                    // "max-age" => $this->maxAge,
                    "path"     => "/",
                    "domain"   => "localhost",
                    "secure"   => true,
                    "httponly" => true,
                    "samesite" => "Lax"
                    
              ];
              if($key == "username") {
                $paramters["httponly"] = false;
              }
            setcookie(
                $key,
                $value,   
                $paramters,
            );
        }
    }
    /**
     * get sesion for checking if the user is existing or not
     */
    public function getSession() {
        $this->sessionID =$_COOKIE["sessionID"];
        $this->sessionToken = $_COOKIE["sessionToken"];
        $this->username_cookie = $_COOKIE['username'];
        // echo $this->sessionID."\n";
        // echo $this->sessionToken."\n";
         
    }

    /**
     * if remember me ticked then refresh session initiated .
     */
    public function isAuthenticated() { //updatepw.api,auth.api,signin.lib
        $this->getSession();

        if(!empty($this->sessionID) && !empty($this->sessionToken) && !empty($this->username_cookie)){
            //This place occupied by caching // but there would be no need because caching make this place more complex , as our query is light weight so no worries

            try {
                /**
                 * get first if not set first
                 */
                $isCached = $this->isSessionCached($this->sessionID);
                if(!empty($isCached)){
                    $cached_token = $isCached["session_token"];
                    $cached_username = $isCached["username"];
                    if($this->sessionToken == $cached_token && $this->username_cookie == $cached_username ){
                        // echo "again this hapen";
                        // return true;
                        return $isCached;
                    }else {
                        throw new \clientException(ErrorCode:"1207");
                    }
                }
                $query = "SELECT * FROM 
                `sessions` WHERE sessionid='$this->sessionID'AND
                 session_token='$this->sessionToken' AND username='$this->username_cookie' ";
                
                $result = $this->getConn()->query($query);
                $result->setFetchMode(\PDO::FETCH_ASSOC);
                $result = $result->fetch()?:array();

                if(count($result) > 0) {

                    /**
                        * -2 - already toExpire function done , only needs new login
                        * -1 - need toExpire function , needs new login
                        *  0 -  refresh session needed
                        *  1 - both are alive , you continue
                     */
                    $isExpired = $this->isExpired($result);

                    $result_sessionID = $result["sessionid"];
                    $result_username = $result["username"];
                    if($isExpired == 1) {

                        $this->cacheSession($result_username,
                        $this->sessionID,
                        $this->sessionToken);

                        $resp = [
                            "username"=>$result_username,
                        ];
                        return $resp;
                    }else if($isExpired == 0) {
                        // echo "is it works 0";
                        
                        return $this->refreshSession($result_sessionID,$result_username);
                    }else if($isExpired == -1) {
                        // echo "is it works -1";
                        // return "need toExpire function , needs new login";

                        $this->toExpire($result_sessionID);
                        return false;

                    }else if($isExpired == -2) {

                        $this->toExpire($result_sessionID,0,NULL);
                        return false;
                    }
                    else if ($isExpired == -3) {

                        // echo "is it works -2";
                        // "already toExpire function done , only needs new login";
                        return false;
                    }else {
                        // echo "is it works 2";
                        // return "already toExpire function done , only needs new login";
                        return false;
                    }

        
                }else {
                    return false;  // dont throw error here, this false means session or session token is invalid
                }
              }catch(\PDOException $e) { //This is more subclassification as of now it is okay
                logg(file:"db_err",exception_: $e);
                throw new \databaseException(ErrorCode:"1803");
            }
            
        }
    }
    /**
     * @param result
     * 
     * -2 -  already toExpire function done , only needs new login
     * -1 - need toExpire function , needs new login
     *  0 -  refresh session needed
     *  1 - both are alive , you continue
     */
    public function isExpired($result) {
    
       
        # code...
        $s_expiry_utc = $result["s_expiry_utc"];
        $s_status = $result["s_status"];
        $r_status = $result["r_status"] ?? 0;
        $r_expiry_utc = $result["r_expiry_utc"] ?? 0;

        if($s_status == 0 && $r_status == 0) { //expired
            // relogin needed , expire function already happened no need to initat  to expire function
            return -3;
        }else if($s_status == 0 && $r_status == 1){
            // refresh also we have make a check whether refresh utc time lessr than the currect time
            if(timeManager::utcNowSeconds() > $r_expiry_utc) { // expired    
                //toExpire
                //needs relogin
                return -1;      
            }
            return 0;
        }else if ($s_status == 1 && $r_status == 1){
            $r_bool = timeManager::utcNowSeconds() > $r_expiry_utc;
            $s_bool = timeManager::utcNowSeconds() > $s_expiry_utc;
            if( $r_bool && $s_bool ) { //if both are true they are expired 
                // to  expire 
                // need relogin
                return -1;
            }else if( $r_bool || $s_bool){
                switch (true) {
                    case $r_bool: //This situation never occur , if this needs to happen both time has to be expired
                        return -1;
                    case $s_bool:
                        //refresh session
                        return 0;
                    default:
                        //refresh session
                        return 0;
                }
            }else { //if both are false
                return 1;
            }
            return 1;
        }else {  //$s_status == 1 && $r_status == 0 (actually this would be NULL becaause uuser didnt used  remember me)
            if(timeManager::utcNowSeconds() > $s_expiry_utc) { // expired    
                //toExpire
                // if there is remember me ticked ,
                //  no situation makes r_status = 0  and s_Status = 1 , so that r_status is NULL our above property logic changed it to 0
                //needs relogin
                return -2;      
            }
            return 1;
        }
        return true;
       

    }
    /**
     * used for logout 
     * Expiring session 
     * it can have ability to expire the session token of the session and refresh token of the session
     */
    public function toExpire($sessionID,$s_status = 0,$r_status = 0) {
        $query = "UPDATE `sessions` SET s_status = :s_status , r_status = :r_status WHERE sessionid = :sessionid";
        $prepared_stmt = $this->getConn()->prepare($query);

        $prepared_stmt->bindParam(":s_status",$s_status);
        $prepared_stmt->bindParam(":r_status",$r_status);
        $prepared_stmt->bindParam(":sessionid",$sessionID);

        $prepared_stmt->execute();

    }
}



?>