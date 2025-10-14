<?php
namespace library;
/**
 * https://nominatim.openstreetmap.org/reverse?lat=12.97194&lon=77.59369&format=json
 * https://geocoding-api.open-meteo.com/v1/search?name=Bangalore
 * https://api.open-meteo.com/v1/forecast?latitude=9.0779&longitude=77.3452&hourly=temperature_2m,relative_humidity_2m,dew_point_2m,precipitation,precipitation_probability,apparent_temperature,rain,showers,snow_depth,snowfall,weather_code,pressure_msl,surface_pressure,cloud_cover,cloud_cover_low,cloud_cover_mid,cloud_cover_high,visibility,evapotranspiration,et0_fao_evapotranspiration,vapour_pressure_deficit,temperature_180m,temperature_120m,temperature_80m,wind_gusts_10m,wind_direction_180m,wind_direction_120m,wind_direction_80m,wind_direction_10m,wind_speed_180m,wind_speed_120m,wind_speed_80m,wind_speed_10m
 * use redis - geo radius,geoadd
 * In our db we having lat , lng in one decimal , two decimal, more decimal values
 * but we gonna use 0.045 for all
 */
define("LOCATION_PREFIX","location_coordinates");
include_once __DIR__."/../config/db_conn.php";
include_once __DIR__."/ExceptionHandler.php";
include_once __DIR__."/log.lib.php";
include_once __DIR__."/../utils/cache.php";
require_once __DIR__."/../vendor/autoload.php";

use function \library\logg;
class weather {
    public $paramaters = ["latitude","longitude","hourly"];// latitude=9.9285&longitude=78.0937&hourly=
    public $period_parameters = ["temperature_2m","relative_humidity_2m","dew_point_2m","apparent_temperature","precipitation_probability","precipitation","rain","showers","snowfall","snow_depth","weather_code","pressure_msl","surface_pressure","cloud_cover","cloud_cover_low","cloud_cover_mid","cloud_cover_high","visibility","evapotranspiration","temperature_80m","temperature_120m","temperature_180m","et0_fao_evapotranspiration","vapour_pressure_deficit","wind_speed_10m","wind_speed_80m","wind_speed_120m","wind_speed_180m","wind_direction_10m","wind_direction_80m","wind_direction_120m","wind_direction_180m","wind_gusts_10m"];
    public $client_location_data=[];
    public $response_stack=[];
    public $something;

    public $data_types = [
            'application/json',
            'application/ld+json',
            // 'application/xml',
            // 'application/yaml',
            // 'text/yaml',
     ];
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
    public function setCoordinates ($request) :void {
        $this->client_location_data["lat"] = (string)$request["latitude"];
        $this->client_location_data["lng"] = (string)$request["longitude"];
        $this->client_location_data["location"] = (string)$request["location"] ?? NULL;
    }
    public function setsomething() {
        $this->something ="admin";
    }

    /**
     * 
     * Return the coordinates of client requested
     * @param mixed $request
     * @return void
     */
    public function getCoordinates ():array {
        return $this->client_location_data;
    }

    public function fetchNearbyData (array $location_coordinates,$safe_limit = NULL)  //Async
    { 
        
        if($safe_limit ==NULL) {
            $safe_limit = count($location_coordinates) -1;
        }
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",[".env"]);
        $dotenv->load();
        $baseURL = $_ENV["WEATHER_API_BASE_URL"];
        $path = $_ENV["WEATHER_API_PATH"];
       
        try{    
            $client = new \GuzzleHttp\Client(
                ["timeout"=> 60.0,]  //20 because we are getting big data
            ); 

            $promises = [];

            foreach ($location_coordinates as $coord) {
                $query = http_build_query([
                    'latitude'  => $coord["latitude"],
                    'longitude' => $coord["longitude"],
                    'hourly'    => implode(',', $this->period_parameters),
                ]);
                $url = "{$baseURL}{$path}?{$query}";
                $promises[] = $client->getAsync($url);
            }

            $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();
            $this->unpackPromisables($responses,$safe_limit);
            return true;

        }catch(\GuzzleHttp\Exception\ClientException $e){
            $response = $this->guzzleErr($e);
            logg(file:"server_err",message: $response);

        }catch(\GuzzleHttp\Exception\ServerException $e){
            $response = $this->guzzleErr(($e));
            logg(file:"server_err",message: $response);
        
        }catch(\GuzzleHttp\Exception\ConnectException $e){
            $response = $this->guzzleErr(($e));
            logg(file:"server_err",message: $response);
        }catch(\Throwable $e){
            logg(file:"server_err",exception_: $e);
        } 

        return false;
    

    }
    public function unpackPromisables ($promisable_responses,$safe_limit) {
        // removing udplcaites is good idea , for our project removeing that outside is perfect to store them by refernce
        // $remove_duplicates= [];
        $count =0;
        $duplicate_find_stack = [];
        foreach($promisable_responses as $key =>$value ){
            
            $state = $value['state'];
            if($state === "fulfilled") {
                $val = $value['value'];
                $body = $val->getBody()->getContents();
                if( $this->isJson($body) ){
                    $body = json_decode($body, true);
                    $lat_lng =  $body["latitude"] &&$body["longitude"] ? [$body["latitude"] ,$body["longitude"] ] : NULL;
                    if(isset($lat_lng) && $count <= $safe_limit) {
                        if(in_array($lat_lng, $duplicate_find_stack)) {
                            $ref_idx = array_search($lat_lng,$duplicate_find_stack);
                            echo "\ndup".$count;
                            $ref = ["REF" => $ref_idx ];
                            array_push($this->response_stack,$ref);
                        }
                        else {
                            echo "\nreal".$count;
                            array_push($duplicate_find_stack, $lat_lng);
                            array_push($this->response_stack,$body);  

                        }   

                        
                        // if (!in_array($lat_lng, $remove_duplicates)) { 
                        //     array_push($remove_duplicates,$lat_lng);
                        //     array_push($this->response_stack,$body);    
                        //     var_dump($body["latitude"] ,$body["longitude"]);
                        //     // $this->cacheThem($body["latitude"] ,$body["longitude"],$body);
                        // }
                    }else if (isset($lat_lng) && $count > $safe_limit) {
                        // echo "Here never reached";
                        array_push($this->response_stack,$body);
                    }

                
                }
                 //and some more elif condition for xml,and other types
                
            }
            else if ($state == "rejected") {
                $exception = $value['reason'];
                if (method_exists($exception,'hasResponse')) {
                    $response = $exception->getResponse();
                    $body  = $response->getBody()->getContents();
                    $contentType = $response->getHeaderLine("Content-Type");
                    if($this->isJson($body)){
                        
                        array_push($this->response_stack,$body);
                    } //and some more elif condition for xml,and other types
                    

                } 

            }
            
        $count++ ;
        }
        // var_dump( $remove_duplicates );
        return true;

    }
    public function isJson($val) {
     
        json_decode($val);
        if(json_last_error() === JSON_ERROR_NONE) {
            return true;
        }

    }
    /**
     * 168 values (7 days × 24 hours)
     * timestamps in UTC
     * temperature for each hour
     * time[i] matches temp[i]
     * we should alwaus open-meto with cooridnate in front-end we get the city name, coordites via
     * https://geocoding-api.open-meteo.com/v1/search?name=Bangalore
     * @return void
     */
    public function fetchWeatherOndemand () { 
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",[".env"]);
        $dotenv->load();
        
        $baseURL = $_ENV["WEATHER_API_BASE_URL"];
        $path = $_ENV["WEATHER_API_PATH"];
    
        $coords = $this->getCoordinates();
        // var_dump($coords);
        $query = http_build_query([
            'latitude'  => $coords['lat'],
            'longitude' => $coords['lng'],
            'hourly'    => implode(',', $this->period_parameters)
        ]);
        try{    
            $client = new \GuzzleHttp\Client(
                ["timeout"=> 20.0,]
            ); 
            $url = "{$baseURL}{$path}?{$query}";
            $response = $client->request("GET", $url);
            if(method_exists($response,"getBody")){
                $response = $response->getBody();
                $resp = $response->getContents();
                return $resp;
            }else {
                return false;
            }
        }catch(\GuzzleHttp\Exception\ClientException $e){
            $response = $this->guzzleErr($e);
            logg(file:"server_err",message: $response);

        }catch(\GuzzleHttp\Exception\ServerException $e){
            $response = $this->guzzleErr(($e));
            logg(file:"server_err",message: $response);
        
        }catch(\GuzzleHttp\Exception\ConnectException $e){
            $response = $this->guzzleErr(($e));
            logg(file:"server_err",message: $response);
        }catch(\Throwable $e){
            logg(file:"server_err",exception_: $e);

            
        }   
        return False; //In API phase , else false means throw unknown error occurred
    }
    public function guzzleErr($e) {
        if(method_exists($e,"hasResopnse")){
            $response = $e->getResponse();
        }else {
            $response = $e->getMessage();
        }
        return $response;
    }
    /**
     * getting nearby regions with the lat and log , used to suggest the user to view there
     * note:the coordinates should be get from the request inital request from the client
     * 5km - 6km radius are 0.045 
     * mostly they all are similar some may show 0.045 as 5km some show 0.0366 becaause it is differing for cities better we use will below
     * In haversine we got excate calc - 0.0366 for lat
     * 0.0254 - for longitude.  both are less than 0.045 if i you get confused, see this i converted them to two decimalas 0.0366 as 0.037 okay and 0.0254 as 0.025
     * calculate 10 cities around 5km backward and forth
     */
    public function find_nearby_location_coordinates ($lat =0 , $lng = 0) {
        $lat_5km = 0.0366;
        $lng_5km = 0.0254;
        echo ($lat."   ".$lng);
        $lat_add = (float) $lat;
        $lng_add = (float) $lng;

        $lat_minus = (float) $lat;
        $lng_minus = (float) $lng;

        $location_coordinates = [];
        if(isset($lat) && isset($lng)) {
            for ($i=0;$i<5;$i++) {
                $lat_add += $lat_5km;
                $lng_add += $lng_5km;

                $location_coordinates[$i] = ["latitude" => $lat_add,"longitude" =>$lng_add];
            }
            for ($j=5;$j<10;$j++) {
                $lat_minus -= $lat_5km;
                $lng_minus -= $lng_5km;
                $location_coordinates[$j] = ["latitude" => $lat_minus, "longitude" =>$lng_minus];
            }
            return $location_coordinates;
        }
        return false;
    }
    /**
     * zero record found no problem but log it
     * after the frist on-demand , either be a location clickd or GPS of them 
     * this based on state or province or country
     * example : user clicked coimbatore locatation , 10 cities of tamilnadu data were donwloaded and stored in redis
     * example2: if GPS clicked in usilampatti, check the usillapatti is avaible  
     * anything can done after getting the exact location name 
     * no record found no problem
     */
    public function get_nearby_top_cities ($location)  {
        $query = "SELECT t.city_name,t.latitude,t.longitude FROM cities_tier_list AS t LEFT JOIN cities_db AS c 
        ON c.state=t.state WHERE c.city=:location_city AND t.latitude IS NOT NULL AND t.longitude IS NOT NULL";
        $conn = $this->getConn();
        $prep = $conn->prepare($query);
        $prep->bindParam(":location_city", $location);
        $prep->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $prep->execute();
        if($res) {
            $result = $prep->fetchAll();
            if(count($result) > 0) {
                return $result;
            }else {
                return false;
            }
        }
        return false;

    }
    public function get_nearby_top_cities_by_coordinates ($lat,$lng):array  {
        $query = "SELECT t.latitude,t.longitude FROM cities_tier_list AS t LEFT JOIN cities_db AS c 
        ON c.state=t.state WHERE t.city_name IS NOT NULL AND c.latitude=:latitude AND c.longitude=:longitude";
        $conn = $this->getConn();
        $prep = $conn->prepare($query);
        $prep->bindParam(":latitude", $lat);
        $prep->bindParam(":longitude",$lng);
        $prep->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $prep->execute();
        if($res) {
            $result = $prep->fetchAll();
            if(count($result) > 0) {
                return $result;
            }else {
                return [];
            }
        }
        return [];

    }
    /**
     * consider out server in india , so we are getting , indian 50 cites 
     * before that note you have get the user location while signin up
     * deafault is getting the top cities of the place of server location
     * if your code runs multiple region that region top cities has to be collected
     * based on tier 1 (optional tier 2)
     * zero record found no problem but LOG them 
     * requested cities -  FOR CRON YOU NEED MORE CITIES SO THAT TIME ,USE MORE REQUESTED CITIES LIEK 100 OR 100O
     */
    public function get_default_top_cities (int $requested_cities = 50,$region ="India") { //india only in starting cron,starting only , it means the intaill point of cron
        $query="SELECT c.city,c.latitude,c.longitude FROM cities_db AS c INNER JOIN cities_tier_list AS t ON c.city=t.city_name WHERE t.tier=1  AND t.country=:country GROUP BY c.city"; //OR t.tier=2
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
    /**
     * 
     * our current server placed locations
     * @return void
     */
    public function get_server_location () {

    }
    /**
     * caching can throw erro cache this before exploding
     * Storing key should be exact decimal , not in two or one (for 99% accurate result)
     * $lat_5km = 0.0366;
     * $lng_5km = 0.0254;
     * @return void
     */
    public function cacheThem($lat,$lng,$value,$bucket_prefix =NULL) { 
        try{

            $lat = (string) $lat;
            $lng = (string) $lng;
            
            if(gettype($value) == "array") {
                $value = json_encode($value);
            }
            $default_expiry = 3600; //1hr
            $key =  $lat .",". $lng;
            if($bucket_prefix != NULL) {
                $key= $bucket_prefix .":". $key;
                // echo "Here Reached Once   ".$key;

            }
            $cache = new \utils\cachelib();
            $cache->setStringCache($key,[$value],0,LOCATION_PREFIX); //unfortunately i set the third parameter expiry here , if removed it lot of places affected , so i put 0 here dont confused 
            $cache->setExpiry($key,$default_expiry,LOCATION_PREFIX);
        }catch(\Throwable $e) {
            logg(file:"server_err",message: $e->getMessage());
            return false;
        }
   
    }
    public function isCached ($lat,$lng, $bucket_prefix = NULL) {
        try {
            $lat = (string) $lat;
            $lng = (string) $lng;
            $key =  $lat .",". $lng;
            if($bucket_prefix != NULL) {
                $key= $bucket_prefix .":". $key;
            }
            $cache =  new \utils\cachelib();
            return $cache->isCached($key,LOCATION_PREFIX);
        }catch(\Throwable $e) {
            logg(file:"server_err",message: $e->getMessage());
            return false;
        }


    }
    public function getFromCache ($lat,$lng,$bucket_prefix = NULL) {
        try {
            $lat = (string) $lat;
            $lng = (string) $lng;
            $key =  $lat .",". $lng;
            if($bucket_prefix != NULL) {
                $key= $bucket_prefix .":". $key;
            }
            $cache =  new \utils\cachelib();
            return $cache->getStringCache($key,LOCATION_PREFIX);
        }catch(\Throwable $e) {
            logg(file:"server_err",message: $e->getMessage());
            return false;
        }

    }
    public function delCache ($lat,$lng) {
        try{
            $lat = (string) $lat;
            $lng = (string) $lng;
            $key =  $lat .",". $lng;
            $cache = new \utils\cachelib();
            return $cache->delCache($key,LOCATION_PREFIX);
        }catch(\Throwable $e) {
            logg(file:"server_err",message: $e->getMessage());
            return false;
        }
    }
    public function clearCache () {
        try{
            $cache = new \utils\cachelib();
            return $cache->flushCache();
        }catch(\Throwable $e) {
            logg(file:"server_err",message: $e->getMessage());
            return false;
        }
    }
    /**
     * we gonna design our own algorithm 
     * bucketed range finder O log N sometime O log
     * backet name example if the coordinate is 12.657 ,77.675 then the actuall bucket is [11,12,13-76,77,]
     * 
     */
    public function in_range($lat,$lng) {

    }
    /**
     * Result 
     * @param mixed $lat
     * @param mixed $lng
     * @return void
     */
    public function getBucketPrefix ($lat,$lng):string{
        $lat = (int) $lat;
        $lng = (int) $lng;

        $back_lat = $lat -1;
        $back_lng = $lng -1;

        $for_lat = $lat+1;
        $for_lng = $lng+ 1;

        $lat_buck = [$back_lat,$lat,$for_lat];
        $lng_buck = [$back_lng,$lng,$for_lng];

        $lat_buck = implode(",",$lat_buck);
        $lng_buck = implode(",",$lng_buck);

        $bucket_prefix = $lat_buck."-".$lng_buck;
        return $bucket_prefix;
    }
    /**
     *  should result three bucket prefix of array .length of three
     * 
     * @param mixed $lat
     * @param mixed $lng
     * @return void
     */
    public function getAllPossibleBucket ($lat,$lng){
        $all_possible_buckets = [];
        $possilbilty_creator = [0,1,-1];

        foreach ($possilbilty_creator as $key => $value) {
            $possible_buckets = $this->geBucketPrefix($lat+$value,$lng+$value);
            array_push($all_possible_buckets, $possible_buckets);
        }

    }



}


// $w = new weather();
// $top_cities = $w->get_default_top_cities();
// var_dump($top_cities);
// $nearby_top_cities = $w->get_nearby_top_cities($location = "Tenkasi");
// var_dump( $nearby_top_cities );
// $lat = 12.9716;
// $lng = 77.5946;
// $calc_nearby_coordinates = $w->find_nearby_location_coordinates($lat, $lng);
/**| # | Latitude | Longitude |
$lat = 12.9716; use this as example that is MG road banglore
$lng = 77.5946;
| #  | Latitude | Longitude | Distance (km) |
| -- | -------- | --------- | ------------- |
| 1  | 13.0082  | 77.6200   | 4.913         |
| 2  | 13.0448  | 77.6454   | 9.826         |
| 3  | 13.0814  | 77.6708   | 14.738        |
| 4  | 13.1180  | 77.6962   | 19.650        |
| 5  | 13.1546  | 77.7216   | 24.562        |
| 6  | 12.9350  | 77.5692   | 4.913         |
| 7  | 12.8984  | 77.5438   | 9.826         |
| 8  | 12.8618  | 77.5184   | 14.740        |
| 9  | 12.8252  | 77.4930   | 19.654        |
| 10 | 12.7886  | 77.4676   | 24.568        |

 */
// var_dump( $calc_nearby_coordinates );
// $request = [];
// $request["lat"] = 12.9716;
// $request["lng"] = 77.5946;
// $request["location"] = "Banglore"; //can be null? and in front end it shoudl try fetch with coordinates details if fails no probelm
// $w->setCoordinates($request);
// $fetch_on_demand = $w->fetchWeatherOndemand();
// var_dump($fetch_on_demand);

// $fetch_nearby_location = $w->fetchNearbyData($calc_nearby_coordinates);
// var_dump($w->response_stack);

// echo $w->isCached(13.125,77.756);
// echo "\n";
// echo $w->delCache(13.125,77.75);
// echo $w->clearCache();


