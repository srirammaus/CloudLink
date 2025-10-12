<?php

namespace library;
require_once __DIR__."/vendor/autoload.php";


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
    public function getUserPreference () {

    }


}