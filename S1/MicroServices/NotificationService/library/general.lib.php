<?php
/**
 * General function which are common for all
 */
namespace library;

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
class genLib {
    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    
    /**
    * check is a valida username throw error
    * check the username limit -50 
    * why im putting all throw here and not putting false , because there are lot of dofferent errors, if i put false in everywhere of isvalidusername and im using isvalidusername 
    * in createuser() then a else condtion happens becuase false occured in isvalidusername ,then how would i know what errro happens 
    * putting flag for each is not a good solution
    * Characters that MUST be escaped (because they have regex meaning):
    * . ^ $ * + ? ( ) [ ] { } | \ /


    * . → matches any char (needs \. to mean a literal dot)

    * ^ → start of string (needs \^ to mean caret)

    * $ → end of string (needs \$ to mean dollar)

    * * + ? → quantifiers (need \* \+ \?)

    * ( ) [ ] { } → grouping & ranges (need \( etc.)

    * | → OR operator (needs \|)

    * \ → itself (needs \\)

    * / → only if / is used as the regex delimiter in PHP (then needs \/)

    * ✅ Characters that do NOT need escaping:
    * a-z  A-Z  0-9  @  _  -  :  ,  ;  !  %  #  =  &  '  "  <  >  ~  `
    */
    
    public function  isValidUsername($username,$email) { // it also check valid email 
        // $username = $this->username;
        // $email = $this->email;

        if(strlen($username) < 50 ) {
            $allowedChars = 'a-zA-Z0-9@_\.\$';
            $allowedChars_ = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/';
            if(!preg_match("/^[$allowedChars]+$/", $username) || !preg_match($allowedChars_, $email)) {
                
                throw new \clientException(ErrorCode:"1005",code:400);
            }else {
                return true;
            }
        }else {
            throw new \clientException(ErrorCode:"1003",code:400);
        }

    }
    public function isValidPhone($phone) {
        // echo $phone;
        if(strlen($phone) < 20) {
            $allowedChars = "/^\+?[1-9][0-9]{7,14}$/";
            if(!preg_match($allowedChars,$phone)){
                throw new \clientException(ErrorCode:"1300");

            } else {
                return true;
            }
        }else {
            throw new \clientException(ErrorCode:"1303");
        }
    }
    /**
     * Check the Existing user based on username ,email
     */
    public function isExistingUser ($username,$email) { // check if there is existing ussername and email associated with it
        $db = new \db\db_conn;
        $conn = $db->conn();
        // echo $this->username."Works fine... here";
        $query = "SELECT username,email FROM users WHERE username='$username' OR email = '$email'";
        $result =  $conn->query($query);

        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $result->fetch()?:array();
        
        // foreach ($result as $key => $value) {
        //     echo "$value\n";
        // }

        if(count($result) > 0){
            return true;
        }

        return false;
    }
    /**
     * Check the Existing Number
     */
    public function isExistingPhone ($phone) { // check if there is existing ussername and email associated with it
        $db = new \db\db_conn;
        $conn = $db->conn();
        // echo $this->username."Works fine... here";
        $query = "SELECT username,email FROM users WHERE phone='$phone'";
        $result =  $conn->query($query);

        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $result->fetch()?:array();
        
        // foreach ($result as $key => $value) {
        //     echo "$value\n";
        // }

        if(count($result) > 0){
            return true;
        }

        return false;
    }
    /***
     * Checks the necessary params are availble
     */
    public function isValidParams(array $arr_1, array $arr_2 ,$optional = NULL) { // you can use ? infornt of array , which gives null if the arr_1 is not type of array
        if($optional === NULL) {
            $a = array_values($arr_1) ;
            $b = array_keys($arr_2) ;
            // echo gettype($a)."\n";
            // echo gettype($b)."\n";
            //==  ignores the order example $a= [a,b] ,$b= [b,a]  . we know both are same , but this gives results in ALSE , so we use array_diff
            //putting $a,$b and $b,$a has difference you will know when you write alternativey
            $res1 = array_diff($a,$b); 
            // echo json_encode($res1);

            if(count($res1) >0 ) {
                return false;
            }
            return true;
        }else {
            $a = array_values($arr_1) ;
            $b = array_keys($arr_2) ;

            $res1 = array_diff($a,$b); 
            // echo json_encode($res1);
            $c = count($res1)."\n";
          
            if($c == $optional ) {
                return true;
            }else if($c < $optional) {
                return NULL;
            }
            else { 
                return false; 

            }
        }
    }
    /**
     * generete array of vulgerWords
     */
    public function vulgerWordChecker($input) {
        $vulgerWords = ["fuck","sex","kill","porn","torture"];

        $pattern = implode("|",$vulgerWords);
        $pattern = "/($pattern)/i";
        // echo $pattern;
        $cmp = preg_match($pattern,$input);
        
        return $cmp;
    

    }
    /**
     * generating a unique ID which for each user
     */
    public function genID($len) {

    }
    /***
     * Generate Token according to the use
     * Most(email or csrf or anything) token generation has no difference
     */
    public static function generateToken ($len = 16,$use =NULL) {
        $token = random_bytes($len);
        $token = bin2hex($token);
        return $token;

    }
    /**
     * gen random number b/w 111111 to 999999
     */
    public static function generateOTP () {
        return rand(111111,999999);
    }
    public function isValidTimeZone ($timezone) {
        $timezones = \DateTimeZone::listIdentifiers();
        if(in_array($timezone,$timezones)){
            return true;
        }
        return false;
    }


}
?>