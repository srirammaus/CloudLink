<?php
set_time_limit(30);
while(true) {
    try{
        echo "Testingg";

        throw new \Exception("Test exception...");
        
    }catch(\Exception) {
        
    }
    continue;
}
?>