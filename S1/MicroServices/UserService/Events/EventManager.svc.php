<?php

namespace Events;
include_once __DIR__."/../library/ExceptionHandler.php";
include_once __DIR__."/../library/log.lib.php";
require_once __DIR__ . '/../vendor/autoload.php';
use function library\logg;
class EventManager {
    public $conf;
    public function __construct() {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",["kafka.env",".env"]);
        $dotenv->load();
    }
    /**
     * set default config
     */
    public function setConfig() {
        $brokers = $_ENV["KAFKA_BROKERS"]; //cluster because single line containing all brokers
        $client_id = $_ENV["CLIENT_ID"];
        //intialize the kafka config
        $conf = new \RdKafka\Conf();

        //setting the timeout
        $conf->set("socket.timeout.ms",60000);
        $conf->set('log_level', (string) LOG_DEBUG);
        $conf->set('debug', 'all');

        //setting the broker or server list ,
        $conf->set("metadata.broker.list", $brokers);

        //setting the client id 
        $conf->set("client.id",$client_id);

        //acks 
        $conf->set("acks","all");

        //retires
        $conf->set("retries",2147483647); // all values mostly i put default in doc

        //security protocol
        $conf->set("security.protocol","plaintext"); //change this to ssl

        $this->conf = $conf;

    }
    /**
     * getting the config we set
     */
    public function getConfig() {
        return $this->conf;
    }
    /**
     * getting health and monitor the server 
     */
    public function getStats(): array{

    }
    public function getProducers () {

    }
    /**
     * get the active consumer
     */
    public function getConsumers() {

    }
    /**
     * clearAll 
     */
    public function clearAll() {

    }
 
    
}

?>