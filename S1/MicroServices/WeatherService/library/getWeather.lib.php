<?php

namespace library;
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/weather.lib.php";
require_once __DIR__."/log.lib.php";

define("WORKER_PREFIX","wokers");

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
     * first checks in the isCached
     * if there return that
     * if not 
     * new on-demand fetch
     * and cache that
     * @return void
     */
    public function searchWeather ($params) {
        try{
            $weather = new \library\weather();
            $weather->setCoordinates($params);
            $lat = $weather->getCoordinates()["lat"];
            $lng = $weather->getCoordinates()["lng"];
            $location = $weather->getCoordinates()["location"] ?? NULL;
            if(isset($lat) && isset($lng)) {
                $isCached = $weather->getFromCache($lat,$lng);
                if(empty($isCached)){
                    $response = $weather->fetchWeatherOndemand();
                    if($response) {
                        var_dump($response);
                        $this->updateQueue($lat,$lng);
                    }
                }else {
                    var_dump($isCached);
                }

            }else {
                throw new \serverException(ErrorCode:"2000");
            }
        }catch (\Throwable $e){
            logg(file:"server_err",exception_:$e);
            throw new \serverException(ErrorCode:"2000");
        }

    }
    /**
     * (string $key ,array $values,bool $head_tail,string $prefix = "default")
     * update the redis queue, this gives tasks to worker
     * @return void
     */
    public function updateQueue ($lat,$lng) {
        $coord = $lat.",".$lng;
        $key = "job_queues";
        $cache =  new \utils\cachelib();
        $cache->setListCache($key,[$coord],true,WORKER_PREFIX);

    }
    


}