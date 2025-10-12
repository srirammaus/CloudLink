<?php 
namespace library;

include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once "sessionManager.lib.php";
class signout  {
    public function  __construct () {

    }
    /**
     * deactivating the sessinn by updating the  s_status and r_status into both are 0
     * if that sessionn is not remember me then
    */
    public function getConn() {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    public function signout () :bool {
        $sessionManager = new \sessionManager;
        $sessionManager->getSession();

        $sessionToken = $sessionManager->sessionID??NULL;
        $sessionID = $sessionManager->sessionID??NULL;

        if($sessionToken == NULL || $sessionID == NULL) {
            throw new \serverException(ErrorCode:"2000");
        }else {
            $query = "UPDATE `sessions` SET s_status=0,r_status=CASE 
            WHEN r_status IS NULL THEN NULL 
            ELSE 0 END WHERE session_token=:session_token  AND sessionid=:sessionid";

            $prep = $this->getConn()->prepare($query);

            $prep->bindParam(":session_token",$sessionToken);
            $prep->bindParam(":sessionid",$sessionID);

            $res = $prep->execute();

            if($res) {
                return true;
            }
            return false;
        }
 
    }
}
?>