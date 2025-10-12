<?php
namespace service\exec;
include_once __DIR__."/../../Events/EventConsumer.svc.php";
include_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__."/../../library/log.lib.php";
include_once __DIR__."/../library/mailer.lib.php";
use function library\logg;
use utils\timeManager;
set_exception_handler (function(\Throwable $e) { 

    // logg(file:"server_err", exception_:$e);
    $packedMessage = [
        "file"=>$e->getFile(),
        "line"=>$e->getLine(),
        "ErrorCode"=> "2000",
        "message"=>$e->getMessage(), 
    ];
    echo json_encode($packedMessage);

}); 
error_reporting(E_ERROR | E_PARSE);

function sendEmailToken () {
    
    $evConsumer = new \Events\EventConsumer;
    $consumer = $evConsumer->createConsumer();
            // Subscribe to topics
    $topics = ["user-events"];
    $consumer->subscribe($topics);

    

    // Wait a bit for partition assignment
    sleep(1);

    // Show assigned partitions
    // $assignment = $consumer->getAssignment();
    // echo "Assigned partitions:\n";
    // foreach ($assignment as $tp) {
    //     // echo "Topic: {$tp->getTopic()} | Partition: {$tp->getPartition()} | Offset: {$tp->getOffset()}\n";
    // }

    
    while (true) {
        try {
            $message = $consumer->consume(1000); // timeout in ms
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    // echo "Received message from Topic: {$message->topic_name}, Partition: {$message->partition}\n";
                    $topic = $message->topic_name;
                    $partition = $message->partition;
                    $payload = $message->payload;
                    $evConsumer->setLogMsg(["topic","partition","payload"],[$topic,$partition,$payload]);
                    if($topic == "user-events") {
                        $payload = get_object_vars( json_decode($payload));
                        print_r($payload);
                        var_dump(gettype($payload));
                        $email = $payload["email"];
                        $email_token = $payload["email_token"];
                        $mail = new \service\library\mailer;
                        $mail->sendMail($email,$email_token);
                    }
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    // No new message in this partition yet
                    echo "End of partition reached\n";

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // Timeout waiting for message
                    echo "Consumer timed out.\n";
                    throw new \Exception("test exception..");
        
                
                default:
                    $msg = rd_kafka_err2str($message->err);
                    $evConsumer->setLogMsg(["message","kafka_err","time"],["something went wrong",$msg,timeManager::utcnow()]);  
                    logg(file:"kafka_err",message: json_encode($evConsumer->logMsg));
                    echo $msg;

            }
        }catch(RdKafka\KafkaErrorException $e) {
            logg(file:"kafka_err",message: $e);

        }catch(\Throwable $e) {
            logg(file:"kafka_err",message: $e);

        }
        continue;
        
  
    }
}

sendEmailToken();



    // Fetch metadata from broker
    // $metadata = $consumer->getMetadata(true, null, 60000);
    // echo "Brokers in cluster:\n";
    // foreach ($metadata->getBrokers() as $broker) {
    //     echo "ID: {$broker->id}, Host: {$broker->host}, Port: {$broker->port}\n";
    // }



       // $consumer->assign([
    //     new \RdKafka\TopicPartition("default-events", 0),
    // ]);  //we set through set rebalance
?>