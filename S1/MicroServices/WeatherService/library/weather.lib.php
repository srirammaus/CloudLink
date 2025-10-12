<?php
namespace library;
/**
 * use redis - geo radius,geoadd
 * In our db we having lat , lng in one decimal , two decimal, more decimal values
 * but we gonna use 0.045 for all
 */

include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
class weather {
    public $paramaters = ["latitude","longitude","hourly"];// latitude=9.9285&longitude=78.0937&hourly=
    public $period_parameters = ["temperature_2m","relative_humidity_2m","dew_point_2m","apparent_temperature","precipitation_probability","precipitation","rain","showers","snowfall","snow_depth","weather_code","pressure_msl","surface_pressure","cloud_cover","cloud_cover_low","cloud_cover_mid","cloud_cover_high","visibility","evapotranspiration","temperature_80m","temperature_120m","temperature_180m","et0_fao_evapotranspiration","vapour_pressure_deficit","wind_speed_10m","wind_speed_80m","wind_speed_120m","wind_speed_180m","wind_direction_10m","wind_direction_80m","wind_direction_120m","wind_direction_180m","wind_gusts_10m"];
    public function __construct () {

    }
     public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    /**
     * Summary of getWeatherOndemand
     * requesting the open-meteo
     * @return void
     */
    public function getWeatherOndemand () {

    }
    public function fetchWeatherOndemand () {
        $client = new \GuzzleHttp\Client(); 
        $baseURL = "";
        $path = "";
        
        $response = $client->request("GET","");
    }
    /**
     * getting nearby regions with the lat and log , used to suggest the user to view there
     * 
     */
    public function find_nearby_location () {

    }
    /**
     * zero record found no problem but log it
     * after the frist on-demand , either be a location clickd or GPS of them 
     * this based on state or province or country
     * example : user clicked coimbatore locatation , 10 cities of tamilnadu data were donwloaded and stored in redis
     * example2: if GPS clicked in usilampatti, check the usillapatti is avaible on statelist, then there 
     */
    public function get_nearby_top_cities ($on_demanded_lat, $on_demanded_lng) {


    }
    /**
     * consider out server in india , so we are getting , indian 50 cites 
     * before that note you have get the user location while signin up
     * deafault is getting the top cities of the place of server location
     * if your code runs multiple region that region top cities has to be collected
     * based on tier 1 or 2
     * zero record found no problem but LOG them 
     * requested cities -  FOR CRON YOU NEED MORE CITIES SO THAT TIME ,USE MORE REQUESTED CITIES LIEK 100 OR 100O
     */
    public function get_default_top_cities (int $requested_cities = 50,$region ="India") { //india only in starting cron,starting only , it means the intaill point of cron
        $query="SELECT c.city,c.latitude,c.longitude FROM cities_db AS c INNER JOIN cities_tier_list AS t ON c.city=t.city_name WHERE t.tier=1 OR t.tier=2 AND t.country=:country GROUP BY c.city";
        $prep = $this->getConn()->prepare($query);
        $prep->bindParam("country", $region);
        $res = $prep->execute();
        $prep->setFetchMode(\PDO::FETCH_ASSOC);
        
        if($res) {
            $result = $prep->fetchAll();
            if(count($result) >$requested_cities) {
                $result = array_slice($result,0,$requested_cities);
                return $result;
            }else if (count($result) < $requested_cities) {
                return $result;
            }else {
                return false; //no record found // no problem
            }
        }else {
            return false; //query execution failed //no problem
        }
    
    }
    public function get_server_location () {

    }
}


$w = new weather();
$top_cities = $w->get_default_top_cities();
var_dump($top_cities);
// $on_demanded_lat = 
// $on_demanded_lng = 
// $nearby_top_cities = $w->get_nearby_top_cities();
