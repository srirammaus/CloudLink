<?php

namespace library;
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/weather.lib.php";
require_once __DIR__."/log.lib.php";

define("WORKER_PREFIX","workers");

use function \library\logg;
/**
 * Rules:
 * The actaull data from the top cities were fetched for every one hour via cron
 * 
 * in national list nothing in rouded but in state list lat and long are rounded
 */
class getWeather {
    public $paramaters = ["latitude","longitude","hourly"];// latitude=9.9285&longitude=78.0937&hourly=
    public $period_parameters = ["temperature_2m","relative_humidity_2m","dew_point_2m","apparent_temperature","precipitation_probability","precipitation","rain","showers","snowfall","snow_depth","weather_code","pressure_msl","surface_pressure","cloud_cover","cloud_cover_low","cloud_cover_mid","cloud_cover_high","visibility","evapotranspiration","temperature_80m","temperature_120m","temperature_180m","et0_fao_evapotranspiration","vapour_pressure_deficit","wind_speed_10m","wind_speed_80m","wind_speed_120m","wind_speed_180m","wind_direction_10m","wind_direction_80m","wind_direction_120m","wind_direction_180m","wind_gusts_10m"];


    public function __construct () {

    }
    /**
     * Default Cron start with tamilnadu cities data
     */
    public function getTopcitieslst () {
    }
    /**
     * 
     * This checks for the on demand data - [A event or message should be emitted in this time synchronusly no acknoloadement needed]
     * first checks in the getCached
     * if there return that
     * if not 
     * new on-demand fetch
     * and cache that
     * @return void
     */
    public function searchWeather ($params,$no_cache=false) {
        try{
            $weather = new \library\weather();
            $weather->setCoordinates($params);
            $lat = $weather->getCoordinates()["lat"];
            $lng = $weather->getCoordinates()["lng"];
            $location = $weather->getCoordinates()["location"] ?? NULL;
            $bucket_prefix = $weather->getBucketPrefix($lat, $lng);
            if(isset($lat) && isset($lng)) {
                $getCached = $weather->getFromCache($lat,$lng,$bucket_prefix);
                if(empty($getCached)){
                    $response = $weather->fetchWeatherOndemand();
                    if($response) {
                        $this->updateQueue($lat,$lng);
                        return json_decode($response);
                    }
                }else {
                    if($weather->isJson($getCached)) {
                        return json_decode($getCached);
                    }else {
                        
                        $get_reference_coord = explode(":",$getCached);
                        $get_reference_coord = $get_reference_coord[1];
                        $get_reference_coord= explode(",",$get_reference_coord);
                        $getCached = $weather->getFromCache($get_reference_coord[0],$get_reference_coord[1],$bucket_prefix);
                        if(!empty($getCached)){
                            return json_decode($getCached);
                        }
                        return false;
                        
                    }
                }

            }else {
                throw new \serverException(ErrorCode:"2000");
            }
        }catch (\Throwable $e){
            logg(file:"server_err",exception_:$e);
            throw new \serverException(ErrorCode:"2000");
        }
        return false;

    }
    /**
     * (string $key ,array $values,bool $head_tail,string $prefix = "default")
     * update the redis queue, this gives tasks to worker
     * @return void
     */
    public function updateQueue ($lat,$lng) {
        $coord = $lat.",".$lng;
        $key = "job_queue";
        $cache =  new \utils\cachelib();
        $cache->setListCache($key,[$coord],false,WORKER_PREFIX);

    }
    
/**Testing 
http://localhost:8082/getWeather.api.php?latitude=13.0082&longitude=77.6200
http://localhost:8082/getWeather.api.php?latitude=12.9716&longitude=77.5946
http://localhost:8082/getWeather.api.php?latitude=19.0760&longitude=72.8777
http://localhost:8082/getWeather.api.php?latitude=28.6139&longitude=77.2090
http://localhost:8082/getWeather.api.php?latitude=17.3850&longitude=78.4867
http://localhost:8082/getWeather.api.php?latitude=22.5726&longitude=88.3639
http://localhost:8082/getWeather.api.php?latitude=26.9124&longitude=75.7873
http://localhost:8082/getWeather.api.php?latitude=9.9312&longitude=76.2673
http://localhost:8082/getWeather.api.php?latitude=11.0168&longitude=76.9558
http://localhost:8082/getWeather.api.php?latitude=23.0225&longitude=72.5714
 */

}