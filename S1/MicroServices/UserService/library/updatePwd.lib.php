<?php
/**
 * You can see here why seperation of concern needs...
 * see there validateWithExistingPwd
 * next there is update pwd..
 * lets say you write the the updatepwd like {this->validateExistingPWd....}
 * and if u want the forgowtPWd.lib to use this udpatePwd function it cant 
 * use because there updatePwd inside it containes the {this->validateExistingPwd()}
 * it becomes the conflict , so both the validatingExistingPwd and updatePwd should wite 
 * independalty and aggrated in API page
 */
namespace library;

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/sessionManager.lib.php";


use \utils\timeManager;
class updatePwd {
    public $storedPwd;
    public $oldPwd;
    public $newPwd;
    public $hashed_pwd;

    public function __construct () {

    }
    public function getConn() {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    public function HashPwd () {
        $SALT = "lkjfsdfksdnfnsdkfnsdkfslkdnfklsdfldsnfndslfnsdklndf";
        $combinedPwd = $this->newPwd.$SALT;
        $this->hashed_pwd = password_hash($combinedPwd,PASSWORD_BCRYPT); //bcrypt algorithm
    }
    /**
     * Im not using unique salt for every user.
     */
    public function cmpPwd ($storedPwd) {
        $SALT = "lkjfsdfksdnfnsdkfnsdkfslkdnfklsdfldsnfndslfnsdklndf";

        $combinedPwd = $this->oldPwd.$SALT;
        return password_verify($combinedPwd,$storedPwd);
    }
    public function validateWithExistingPwd($username,$session_token,$sessionID,$oldPwd) {
        $this->oldPwd = $oldPwd;

        //getting the Existing thought db
        $query = "SELECT u.`password` FROM users u JOIN `sessions` s WHERE
         u.username=:username 
        AND s.session_token=:session_token
        AND s.sessionid=:sessionid ";

        $prep = $this->getConn()->prepare($query);

        $prep->bindParam(":username",$username);
        $prep->bindParam(":session_token",$session_token);
        $prep->bindParam(":sessionid",$sessionID);
        $res = $prep->execute();
        if($res){ //if code executes
            $prep->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prep->fetch()?:array();
            if(count($result) > 0) {
                $this->storedPwd = $result["password"];
                $cmp = $this->cmpPwd($this->storedPwd);
                // echo $cmp."\n";
                //comparing both pwd
                if($cmp) {
                    return true;
                }else {
                    throw new \clientException(ErrorCode:"1100");

                }

            }
            throw new \databaseException(ErrorCode:"1801"); // invalid session token or session id o username
        }else {
            throw new \databaseException(ErrorCode:"1801"); // invalid session token or session id o username
        }
        
        
    }
    /**
     * In api page check whethe the validateWithExistingPwd return true then use this conditio
     * or this function should be used to conditionof forgotpwd api
     */
    public function updatePassword ($username,$newPwd,$sessionID) {
        $this->newPwd = $newPwd;
        $this->HashPwd();
        $reset_time =  timeManager::utcNow();
        $status =1;
        $reset_type = "session";
        //setting the if existing user's email and token to NULL to show this as last made passsword update by session token
        $token = NULL;
        $expiry = NULL;

        //setting the connection and start transaction 
        $conn = $this->getConn();
        $conn->beginTransaction();

        //queries
        $query = "UPDATE users SET `password`=:password WHERE username=:username ";
        $prep = $conn->prepare($query);
        $prep->bindParam(":password",$this->hashed_pwd);
        $prep->bindParam(":username",$username);
        $prep->execute();

        $query = "INSERT INTO reset_table (username,sessionid,status,reset_type,reset_count) VALUE (:username,:sessionid,:status,:reset_type,reset_count) 
        ON DUPLICATE KEY UPDATE username=:username,sessionid=:sessionid,status=:status,
        token=:token,expiry=:expiry,reset_type=:reset_type,reset_count= reset_count + 1";


        $prep = $conn->prepare($query);
        $prep->bindParam(":username",$username);
        $prep->bindParam(":sessionid",$sessionID);
        $prep->bindParam(":status",$status);
        $prep->bindParam(":reset_type",$reset_type);
        $prep->bindParam(":reset_count",$reset_count);

        //if the query would be update below params compulsorly added
        $prep->bindParam(":token",$token);
        $prep->bindParam(":expiry",$expiry);
        

        $prep->execute();

        //executing each queries
        // $prep->exec($query);
        // $prep->exec($query); // you can also use this how do you get row count
        
        //if both queries happen it returns
        $res = $conn->commit();
        
        // $prep = $this->getConn()->prepare($query);
        if($res){ //if code executes
            if($prep->rowCount() > 0) {
                echo $prep->rowCount()." Works \n";
                return true;
            }else {
                echo "Here came";
                throw new \databaseException(ErrorCode:"1803"); //why im not putting invalid username becuase that check already happened in validateExistingPwd so if this rowcount ==0 then it would be query execution error
            }
        }else {
            throw new \databaseException(ErrorCode:"1801"); // invalid session token or session id o username
        }

    }
    /**
     * add reset table how much time reset happens to track
     * later
     */
    public function addResets () {
        
    }
}
?>