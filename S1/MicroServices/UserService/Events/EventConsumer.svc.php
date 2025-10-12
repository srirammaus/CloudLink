<?php
namespace Events;
set_time_limit(0);

include_once __DIR__."/../library/ExceptionHandler.php";
include_once __DIR__."/../library/log.lib.php";
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__."/EventManager.svc.php";

use function library\logg;
use utils\timeManager;
/**
 * consumer must need properties 
 * group id 
 * 
 */
class EventConsumer {   
    public $conf;
    public $consumer;
    public $topic;
    public $flush;
    public $event;
    public $partition;
    public $flag;
    public $key;
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
    public function createConsumer() {
        // Initialize EventManager and get config
        $ev = new EventManager;
        $ev->setConfig();
        $this->conf = $ev->getConfig();

        //set errror cb
        $this->conf->setErrorCb(function($kafka ,$err ,$reason) {
            $this->setLogMsg(["message","kafka_err","time"],["something went wrong",rd_kafka_err2str($err),timeManager::utcnow()]);  
            logg(file:"kafka_err",message: json_encode($this->logMsg));
        });
        // Set group ID for consumer
        // if all need from first, use a unique group id (hack or trick)every time to force "from beginning"
        $this->conf->set("group.id", "php-consumer-mail-service"); 

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'earliest': start from the beginning
        $this->conf->set('auto.offset.reset', 'earliest');

        // Emit EOF event when reaching the end of a partition
        $this->conf->set('enable.partition.eof', 'true');

      
        /***
         * It rebalances the assignment of partion , 
         * consider if we having only one consumer , we can explicitly assign partion like new RdKafka\TopicPartion::assgin("topic 1",1)new RdKafka\TopicPartion::assgin("topic 1",2) new RdKafka\TopicPartion::assgin("topic2,1)  
         * The above means "This consumer - This partion" .. okay what if we have multiple consumer 
         * in same consumer group id , it becomes conflict  ..How ? if you set explicitly the consumer1 - also getting stufs from topic 1 partion same follwed by consumer 2
         * so inconsistency.. setRebalcne solve this by our library intention throws error but the catch 
         * by this callback and firing each rule . A  scenoria if the  Topic: logs below , see down  ///default is only null, dont confused
         */
        $this->conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka ,$err ,array $partitions = NULL){ 
            switch($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    print_r($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }

        });

     

        // Create the KafkaConsumer
        $this->consumer = new \RdKafka\KafkaConsumer($this->conf);

        return $this->consumer;
    }

    public function genConsumerGrpId() {

    }


}

 // $this->conf->setLogCb(function($kafka, $level, $facility, $message) {
        //     // Uncomment if you want to see debug logs
        //     // echo "[Kafka Log] Level:$level Facility:$facility Message:$message\n";
        // });
/**
 *                 Partitions: 0, 1, 2, 3 (4 partitions total)

*  Consumers: C1 and C2 in the same group (group.id = "logProcessors")

*  Step 1: C1 starts consuming
*  $consumer1->subscribe(["logs"]);
*  $consumer1->setRebalanceCb(...);


*  Kafka sees only C1 in the group.

*  It assigns all 4 partitions to C1.

*  Rebalance callback fires:

*  $err = RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS
*  $partitions = [logs-0, logs-1, logs-2, logs-3]
*  $consumer1->assign($partitions); // C1 starts consuming all 4 partitions

*  Step 2: C2 joins the same group
*  $consumer2->subscribe(["logs"]);
*  $consumer2->setRebalanceCb(...);


*  Kafka triggers a rebalance for the whole group because now there are 2 consumers.

*  Kafka decides to evenly split partitions:

*  C1 → partitions 0,1

*  C2 → partitions 2,3

*  What happens internally:

*  Rebalance callback for C1 fires:

*  $err = RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS
*  $partitions = [logs-0, logs-1, logs-2, logs-3]  // old assignment
*  $consumer1->assign(NULL);  // clear old partitions


*  Rebalance callback for C1 fires again:

*  $err = RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS
*  $partitions = [logs-0, logs-1] // new assignment
*  $consumer1->assign($partitions);  // C1 accepts new assignment


*  Rebalance callback for C2 fires:

*  $err = RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS
*  $partitions = [logs-2, logs-3]
    *$consumer2->assign($partitions);  // C2 starts consuming its partitions
 */
?>