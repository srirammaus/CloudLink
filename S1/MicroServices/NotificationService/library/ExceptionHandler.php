<?php
// namespace library;
// use Exception; // there is no need to compulsorily put this, if you want to remove this you have to add  "\" behind every "Exception" object you used

function ErrorCodeMessages ($ErrorCode) :string {
    $codeMessages = array (
    //  Username Errors
    "1000" => "Invalid Username",
    "1001" => "Invalid characters in Username",
    "1002" => "Username too short",
    "1003" => "Username limit exceeded",
    "1004" => "Username already exists",
    "1005" => "Invalid Inputs in Username or Email ",

    //  Password Errors
    "1100" => "Invalid Password",
    "1101" => "Password too short",
    "1102" => "Password too weak",
    "1103" => "Password too long",
    "1104" => "Passwords do not match",

    //  Email Errors
    "1200" => "Invalid Email",
    "1201" => "Invalid characters in Email",
    "1202" => "Email too long",
    "1203" => "Email already registered",
    "1204" => "Email domain not allowed",
    "1205" => "Token Expired",
    "1206" => "Email already verified",
    "1207" => "Invalid Token",
    "1208" => "Email not verified",
    "1209" => "email not sent",

    //  Phone Errors
    "1300" => "Invalid Phone Number",
    "1301" => "Invalid characters in Phone Number",
    "1302" => "Phone number too short",
    "1303" => "Phone number too long",
    "1304" => "Phone number already registered",
    "1305" => "Phone number not verified",
    "1306" => "OTP Expired",
    "1307" => "Number already verified",
    "1308" => "Invalid OTP",

    //  Bio / Profile Fields
    "1400" => "Invalid Bio",
    "1401" => "Invalid characters in Bio",
    "1402" => "Bio too long",
    "1403" => "Bio too short",

    // File Upload / Image Errors
    "1500" => "Invalid File Upload",
    "1501" => "File too large",
    "1502" => "File type not allowed",
    "1503" => "File upload failed",
    "1504" => "Image resolution not supported",

    //  General Validation
    "1600" => "Required field missing",
    "1601" => "Invalid characters in input",
    "1602" => "Input too short",
    "1603" => "Input too long",
    "1604" => "Invalid user",

    //  Authentication & Authorization
    "1700" => "Unauthorized access",
    "1701" => "Invalid session token",
    "1702" => "Session expired",
    "1703" => "Permission denied",
    "1704" => "OTP Expired,initiate new verification request",
    "1705" => "Email Token Expired , initiate new verification request",

    //  Database Errors
    "1800" => "Database connection failed",
    "1801" => "Record not found",
    "1802" => "Duplicate entry",
    "1803" => "Query execution failed",
    "1804" => "No rows inserted",

    //  Network / API Errors
    "1900" => "Network request failed",
    "1901" => "Timeout occurred",
    "1902" => "Invalid API key",
    "1903" => "API rate limit exceeded",
    "1904" => "Parameters Missing",
    "1905" => "captcha failed",
    "1906" => "captcha generation failed",
    "1907" => "Invalid parameters",
    //  Misc
    "2000" => "Unknown error occurred", 
    "2001" => "Invalid request format",
    "2002" => "Feature not supported",
    "2003" => "Internal Server Error",

    
    //type Errors
    "2100" => "Invalid request type ",
    "2101" => "Invalid Timezone",

    //resource errors 
    "2200" => "File Not Found",
    "2201" => "code execution failed",


    //Account erros
    "2301"=>"account deactivated",
    "2302"=> "account deleted",
    "2303"=> "account compromised",

    //session errors 
    "2400" => "session Expired",
    "2401" => "cookie parameters misssing",
    
        //Event Errors
    "2601" => "connection failed",
    "2602" => "message not sent",
    
    );

    return $codeMessages[$ErrorCode]??$codeMessages["2003"];
}
/**
 * Actually the ErrorCode defines whats message send to client while we throwing by ourselvs
 * @param message,code -  default message and default code , this default message sent only in response json body and that code sent response header 400 and [bad request (whihc was provided by REST.api.php)
 * @param Errorcode -  while throwing , we have to put throw new \clientException($ErrorCode = 1000) corresponnding error message sent by getting them from ErrorCode
 * So there is NO custom message sending like throw new \clientException("No custom Err message") , only default message like bad request and Error code message 
 * still there is a possible way to send custom error messsage like above for that , you just repeat same becuase i here mentioned ErrorCode=NULL and in function getMessage having if (ErrorCode == NULL ){return parrent::getMessage()} 
 */



class clientException extends Exception {
    public function __construct($message = "Bad Request" ,$code =400 ,$ErrorCode=NULL,?Throwable $prev = null) {
        parent::__construct($message,$code,$prev);
        $this->ErrorCode = $ErrorCode;
    }
    public function getErrorCode () { 
        return $this->ErrorCode??"999"; //999 is custom messages code
    }
    public function getCustomMessage() :string {
        if($this->ErrorCode != NULL) { //if ErrorCode Present ,Give the Error message by choosing in ErrorCode message functio
            return ErrorCodeMessages($this->ErrorCode);
        }else {
            return parent::getMessage();
        }

    }
   
    public function packedMessage () {
        /**
         * All messages, line , code every thing should be looged including time 
         */
        $packedMessage = [
            "flag"=>parent::getCode(), // our status code , but here i mentioned it as flag dont confused
            "message"=>parent::getMessage(),
        ];
        return $packedMessage; //
    }

}
class databaseException extends Exception  {
    public function __construct($message = "Internal Server Error" ,$code =500 ,$ErrorCode=NULL,?Throwable $prev = null) {
        parent::__construct($message,$code,$prev);
        $this->ErrorCode = $ErrorCode;

    }
    public function getErrorCode () { 
        return $this->ErrorCode??"999"; //999 is custom messages code , means when message customy throw by anywhere
    }
    public function getCustomMessage() :string {
        if($this->ErrorCode != NULL) { //if ErrorCode Present ,Give the Error message by choosing in ErrorCode message functio
            return ErrorCodeMessages($this->ErrorCode);
        }else {
            return parent::getMessage();
        }

    }
    public function packedMessage () {
        // packed as json
        $packedMessage = [
            "flag"=>parent::getCode(),
            "message"=>parent::getMessage(),
        ];
        return $packedMessage;
        // throw new Exception("unknow exception");
    }
}
class serverException extends Exception  {
    public function __construct($message = "Internal Server Error" ,$code =500,$ErrorCode=NULL,?Throwable $prev = null) {
        parent::__construct($message,$code,$prev);
        $this->ErrorCode = $ErrorCode;

    }
    public function getErrorCode () { 
        return $this->ErrorCode??"999"; //999 is custom messages code
    }
    public function getCustomMessage() :string {
        if($this->ErrorCode != NULL) { //if ErrorCode Present ,Give the Error message by choosing in ErrorCode message functio
            return ErrorCodeMessages($this->ErrorCode);
        }else {
            return parent::getMessage();
        }

    }
    public function packedMessage () {
        // packed as json
        $packedMessage = [
            "flag"=>parent::getCode(),
            "message"=>parent::getMessage(),
        ];
        return $packedMessage;
        // throw new Exception("unknow exception");
    }
}
class verificationException extends Exception  {
    public function __construct($message = "verification Error" ,$code =401,$ErrorCode=NULL,?Throwable $prev = null) {
        parent::__construct($message,$code,$prev);
        $this->ErrorCode = $ErrorCode;

    }
    public function getErrorCode () { 
        return $this->ErrorCode??"999"; //999 is custom messages code
    }
    public function getCustomMessage() :string {
        if($this->ErrorCode != NULL) { //if ErrorCode Present ,Give the Error message by choosing in ErrorCode message functio
            return ErrorCodeMessages($this->ErrorCode);
        }else {
            return parent::getMessage();
        }

    }
    public function packedMessage () {
        // packed as json
        $packedMessage = [
            "flag"=>parent::getCode(),
            "message"=>parent::getMessage(),
        ];
        return $packedMessage;
        // throw new Exception("unknow exception");
    }
}
?>