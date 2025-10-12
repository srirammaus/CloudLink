<?php 
/**
 * Connecting php to Mysql db
 */
namespace db;
include_once __DIR__."/../library/ExceptionHandler.php";
include_once __DIR__."/../library/log.lib.php";
require_once __DIR__ . '/../vendor/autoload.php';
  
use function \library\logg;

class db_conn {


    private $hostname ;
    private $username ;
    private $password ;
    private $db ;
    public function __construct () {
        //if you having env in other directories then  "\Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env","db_cred.env");"
        //(__DIR__."/env",["db_cred.env",".env", ])
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",["db_cred.env",".env"]);
        $dotenv->load();
        $this->hostname = $_ENV["DB_HOSTNAME"];
        $this->username = $_ENV["DB_USER"];
        $this->password = $_ENV["DB_PASSWORD"];
        $this->db = $_ENV["DB_NAME"];
    }
    public function conn() {


        try {
            
            $conn = new \PDO ("mysql:host=$this->hostname;dbname=$this->db",$this->username,$this->password);
            $conn->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
        }
        catch (\PDOException $e){
            /**
             * Create a log function put it every where exception thrown
             */
            logg("db_err",exception_: $e);
            throw new \databaseException(ErrorCode:"1800"); // this caught by outside
        }

        return $conn;

    }
}
?>