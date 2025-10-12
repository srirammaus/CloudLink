<?php

namespace library;
include_once __DIR__."./config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/general.lib.php";
include_once __DIR__."/../Events/EventProducer.svc.php";
class tools {
    public function __construct () {

    }
    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }

    public function getTools () {
        $query = "SELECT * FROM `dashboard_tools` ";
        $conn = $this->getConn();
         
        $result = $this->getConn()->query($query);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $result->fetchAll()?:array();
        
        if (count($result) > 0) {
            return $result;
        }
    }
    
}

?>