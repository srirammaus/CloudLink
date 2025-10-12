<?php
//RdKafka\KafkaErrorException - use this to caught error , you dont want use it herer use it c=outside of ibray
namespace Events;
include_once __DIR__."/../library/ExceptionHandler.php";
include_once __DIR__."/../library/log.lib.php";
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__."/EventManager.svc.php";

use function library\logg;
use utils\timeManager;

class EventProducer {
    public $conf;
    public $producer;
    public $topic;
    public $flush;
    public $event;
    public $events = [];
    public $partition;
    public $flag;
    public $key;
    public $logMsg ;
    public function __construct() {
        

    }
    public function setLogMsg($keys,$values) { //singatuer
        $this->logMsg = [];
        foreach ($keys as $key_key => $key) {
            $this->logMsg += [
                "$key" => $values[$key_key],
            ];
        }
    }
    public function createProducer () {
        //init eventmanager
        $ev = new EventManager;
        $ev->setConfig();

        //get the conf
        $this->conf = $ev->getConfig();
        $this->conf->set("max.in.flight.requests.per.connection",5);
        $this->conf->set("enable.idempotence",true); // duplicates avoid , if accidentally sent
        $this->conf->set("delivery.timeout.ms",300000);

        //allow auto create topic
        $this->conf->set("allow.auto.create.topics",true);

        //catching the conf errors or partion err
        $this->conf->setErrorCb(function($kafka ,$err ,$reason) {
            $this->setLogMsg(["message","kafka_err","time"],["something went wrong",rd_kafka_err2str($err),timeManager::utcnow()]);  
            logg(file:"kafka_err",message: json_encode($this->logMsg));
        });

        //setting the delivery msg conflict
        $this->conf->setDrMsgCb(function($kafka ,$msg){
            if($msg->err) {
                $this->setLogMsg(["message","kafka_err","time"],["something went wrong",$msg->errstr(),timeManager::utcnow()]);
                logg(file:"kafka_err",message: json_encode($this->logMsg));
                throw new \serverException(ErrorCode:"2602");
            }else {
                $ack = $msg->offset;
                $key = $msg->key;
                if($ack > 0 || $ack == 0) {
                    //succesfully sent
                    $this->setLogMsg(["offset","keyName","time"],[$ack,$key,timeManager::utcnow()]);
                    logg(file:"event_log",message: json_encode($this->logMsg));
                }else {
                    $this->setLogMsg(["message","time"],["something went wrong",timeManager::utcnow()]);
                    logg(file:"kafka_err",message: json_encode($this->logMsg));
                    throw new \serverException(ErrorCode:"2602");
                }
            }
        });
        //crate new producer
        $this->producer = new \RdKafka\Producer($this->conf);
   

    }
    public function setTopic($topic_name) {
        $this->topic = $this->producer->newTopic($topic_name);  // create if not exist else use the old
    }
  
    public function setEvent($partition=RD_KAFKA_PARTITION_UA,$flag=0,$event_msg = "Event occured" ,$key = NULL) {
        $event = [
            "partition" =>$partition,
            "flag" =>$flag,
            "event_msg" => $event_msg,
            "key"  => $key,
        ];
        array_push($this->events,$event);


    }
    public function emitEvent($timeout = 10000) { // 10000 - 10 seconds   after 10 seconds if the produce req sent or not that produce insrtance is destroyed
    
       
        foreach ($this->events as $key => $event) {
            $this->topic->produce(
                $event["partition"],
                $event["flag"], 
                $event["event_msg"],
                $event["key"],
                );
        }
        $metadata =  $this->producer->getMetadata(true,null,60e3);
         
        while($getlen = $this->producer->getOutQLen() > 0){
            $this->producer->poll(50);    //poll is used when you do task as asyn , if you are using poll no need of using flush, you use it as optional 
            //poll is similar to flush , here tasks are done asynchronous so the setDrMsgCb like all callback has been called , so non blocking the code , poll collects the 
            //delivery report ,acks for each produce (for knowing abt each produce , queued in queue we use getOutQLen , poll which sends the actaull msg to broker, then call the callback function) //

        }
     
        $result = $this->producer->flush($timeout);

        if($result !==  RD_KAFKA_RESP_ERR_NO_ERROR) {
            $err = rd_kafka_err2str($result);
        
            $this->setLogMsg(["message","kafka_err","time"],["something went wrong",$err,timeManager::utcnow()]);  
            logg(file:"kafka_err",message: json_encode($this->logMsg));
            throw new \serverException(ErrorCode:"2601");
        }else {
            $this->setLogMsg(["message","time"],["succefully sent",timeManager::utcnow()]);
            logg(file:"event_log",message: json_encode($this->logMsg));
        }
        return $result; 
    }
    /**
     * while event happening
     */
    public function on_(){

    }
    /**
     * once the event completed
     */
    public function once() {

    }
}
// use this library woth try catch
// $ep = new EventProducer;
// $ep->createProducer();
// $ep->setTopic("default-events");
// $ep->setEvent(0,0,"TEST 1 EVENT","TEST 1 KEY");
// $ep->setEvent(0,0,"TEST 2 EVENT","TEST 2  KEY");
// $result = $ep->emitEvent();

// print($result);