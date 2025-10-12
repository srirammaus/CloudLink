<?php

namespace testservice;

$conf = new \RdKafka\Conf();
$conf->set('socket.timeout.ms', 11);
$conf->set('metadata.broker.list', 'ubuntu.server:9092,ubuntu.server:90094');
$producer = new \RdKafka\Producer($conf);
$topic = $producer->newTopic("my_topic");

$topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload");
$producer->flush(10000);