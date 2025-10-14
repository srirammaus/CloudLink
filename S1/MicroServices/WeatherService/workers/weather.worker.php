<?php

namespace Workers;

require_once __DIR__."/../library/weather.lib.php";
require_once __DIR__."/../library/log.lib.php";
require_once __DIR__."/../utils/cache.php";
require_once __DIR__."/../utils/timeManager.php";

use \utils\timeManager;
use function \library\logg;

define ("WORKER_PREFIX","workers");
class Weather_worker {
    /**
     * 
     * Log the start time while contructing or worker starting
     */
    public function __construct() {
        logg(file:"backend_log",message:"The Worker Started At : ".timeManager::utcNow());

    }
    /**
     * Summary of work
     * revice the coordinate from the job_queue(key) using BRPOP [for implement this step as queue i m using BRPOP is here and while updating LPUSH]
     * then you have to process the one coordinate with coorresponding function in weather.lib.php
     * 1.get the nearby top cities coordinates  - 10 cities
     * 2.get the nearby coordinates - 10 cities (total 25 km radius, per city 5km radius)
     * 3. then fetch those all
     * 5. find the duplicates
     * 4. save the mutual values with reference
     * 
     * 
     * @return void
     */
    public function work ($coords) {
        if(is_array($coords)){
            if(array_key_exists(1, $coords)){
                $coord = explode(",",$coords[1]);
                $lat = $coord[0];
                $lng =  $coord[1];
            }
            $weather = new \library\weather();
            $nearby_top_cities_coords =  $weather->get_nearby_top_cities_by_coordinates($lat,$lng);
            $nearby_location_coords = $weather->find_nearby_location_coordinates($lat,$lng);
            
            $current_coord = [["latitude" => $coord[0],"longitude" => $coord[1]]];
            // var_dump($nearby_location_coords);
            //merge array
            $current_and_nearby_count = count($nearby_location_coords) + count($current_coord);
            $safe_limit = $current_and_nearby_count-1;
            $merged_coords = array_merge($nearby_location_coords,$current_coord,$nearby_top_cities_coords);  //if you are using nearby cities , you have to use safe limit
            var_dump($merged_coords);
            echo $safe_limit;
            $responses = $weather->fetchNearbyData($merged_coords,$safe_limit);
            if($responses){
                $bucket_prefix = $weather->getBucketPrefix($coord[0],$coord[1]);
                $resp = $weather->response_stack;
                // print(json_encode($resp));
                $count = 0;
                forEach($resp as $key => $value) {
                    /**
                     * dont confused by seeing redis values ,look like seems something wrong, but that is correct 
                     * becuase in redis that is in ascending order 
                     * so all things are stored correct
                     * for cross check check $count below and print_r($value) you doubdt will be clarified , use this value to atitude=12.9716&longitude=77.5946 clear doubt.
                     * 
                     */
                    // if(isset($value["REF"])){
                    //     echo "\n".$count;
                    //     echo "\n";
                    //     var_dump($merged_coords[$count]);
                    //     print_r($value);
                    // }
                    $lat_key = $merged_coords[$count]["latitude"];
                    $lng_key = $merged_coords[$count]["longitude"];
                    if(!$weather->isCached($lat_key,$lng_key,$bucket_prefix) && !$weather->isCached($lat_key,$lng_key)){
                        //cache them
                        if($safe_limit < $count){
                            $bucket_prefix = NULL;
                        }
                        if(isset($value["REF"])){
                            $reference_val = (int)$value["REF"];
                            $value ="REF:".$merged_coords[$reference_val]["latitude"].",".$merged_coords[$reference_val]["longitude"];
                        }
                        $weather->cacheThem($lat_key,$lng_key,$value,$bucket_prefix);
                    }
                    $count++;
                }
            }else {
                logg(file:"backend_log",message:"something went wrong while downloading this coordinates ".$coords[1]." ".timeManager::utcNow());
            }


        }



    }
  
    public function run () {
        $cache = new \utils\cachelib();
        $key = "job_queue";
        echo "Started working : \n";
        while(1){
            $coords = $cache->getBRPOP($key,WORKER_PREFIX);
            if ($coords !== null && $coords !== '' && $coords !== []) {
                // valid coordinate
                $this->work($coords);
            }

        }
    
    }

    /**
     * 
     * Log the end time while the worker ending
     */
    public function __destruct() {
        logg(file:"backend_log",message:"The Worker Started At : ".timeManager::utcNow()."\n");
    }
}

$worker = new Weather_worker();
$worker->run();
?>