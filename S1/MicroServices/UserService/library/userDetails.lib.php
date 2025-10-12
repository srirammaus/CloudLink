<?php 
namespace library;

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/sessionManager.lib.php";
include_once __DIR__."/../utils/cache.php";

use \utils\timeManager;

class userDetails {


    public function __construct() {

    }
    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    /**
     * In APi page the Cookies has to be validated
     * limit the data and dole
     */
    public function getuserDetails ($username) {
        if(!$username ){
            throw new \clientException(ErrorCode:"1907");
        }
        $query = "SELECT * FROM `users` WHERE username=:username";

        $prepare_stmt = $this->getConn()->prepare($query);
        $prepare_stmt->bindParam(":username",$username);
        $res = $prepare_stmt->execute();

        if($res) {
            $prepare_stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $prepare_stmt->fetch()?:array();
            if(count($result) > 0) {
                return $result;
            }
        }

        return False;

    }



}



?>