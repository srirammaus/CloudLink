<?php
  /**
     * verify it in redis db and udpate the password and update the password reset table 
     */
namespace library;

include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once "sessionManager.lib.php";
include_once "updatePwd.lib.php";

use \utils\timeManager;

class resetPwd {
  public $reset_time;
  public $username;
  public $token;
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
   * This function depends on udpatePwd.lib.php
   */
  public function resetPwd ($username,$newPwd) {
      $this->username = $username;

      $updatePwd = new updatePwd;
      $resetPwd =  $updatePwd->updatePassword($username,$newPwd); // password change when this happens, more than other are server resposniblty

      return $resetPwd;

  }
  /**
   * username  - username
   * token - the generated token
   * expiry - the expiry time of token . in UTC seconds
   * status - 0 is not used , 1 means token used , -1 expired -( this work also shared by event scheduler 
   * reset Time takes place here
   * 
   * if pwd udpated then 
   */
  public function updateResets($username,$token,$newPwd) {
    $this->newPwd = $newPwd;
    $this->token = $token;
    $this->HashPwd();

      //expiry >VALUES(expiry),
    $this->username = $username;
    $this->reset_time = timeManager::utcNow();
    $currentTime = timeManager::utcNowSeconds();
    $status = 1;
    $old_status = 0;
    //update in both table
    $query = "UPDATE reset_table r
          JOIN users u ON r.username = u.username
          SET 
              r.status   = :status,
              r.reset_time= :reset_time,
              r.reset_count = r.reset_count+1,
              u.password = :password
          WHERE 
              u.username = :username AND 
              r.token = :token
              AND r.expiry > :expiry AND r.status =:old_status";
    
    $prep = $this->getConn()->prepare($query);


    $prep->bindParam(":password",$this->hashed_pwd);
    $prep->bindParam(":status",$status);
    $prep->bindParam(":reset_time",$this->reset_time);

    $prep->bindParam(":username",$this->username);
    $prep->bindParam(":token",$this->token);
    $prep->bindParam(":expiry",$currentTime);
    $prep->bindParam(":old_status",$old_status); // if existing status zero only you have to let the user use this token


    $res = $prep->execute();
    if($res) {
      $updatedRow = $prep->rowCount();

      if($updatedRow > 0) {
        return $resp = [
          "flag" => "1",
          "message" => "password changed sucessfully",
        ];
      }else {
        throw new \clientException(ErrorCode:"1205");
      }

    }else {
      // echo "failes";
      throw new \databaseException(ErrorCode:"1803");
    }

  }
}

?>