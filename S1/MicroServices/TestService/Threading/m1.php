<?php
use \parallel\{Runtime, Future, Channel, Events};
$runtime_thread =  new Runtime();
// runtime vs future . future is like promis here below see im returning done . In runtime you return anything just the thread runs as usaul. 
//all futurre cotinas runtime,but all runtime not be a future
//while using run we adding the runtime to the pool
$future = $runtime_thread->run(function() {
    for($i=0;$i<5000;$i++) {
        echo "*";
        // usleep(100000);
    }
    return "Done";
});

for($i=0;$i<5000;$i++) {
        echo ".";
        // usleep(100000);
}
echo $future->value();
?>