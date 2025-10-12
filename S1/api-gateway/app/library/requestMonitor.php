<?php
/**
 * Using prometheus for this metrics
 */
namespace App\library;
use GuzzleHttp\Client;

use Illuminate\Http\Request;
use Prometheus\RenderTextFormat;

use Prometheus\CollectorRegistry;
use Illuminate\Support\Facades\Redis;

/**
 * monitor most visited service api
 * request response times
 * Throughput
 * Error rates per service
 * Resource utilization of this API gateway
 * Blocked request IP 
 * 
 * Rate limiting
   *Middleware (e.g., ThrottleRequests) 
   *Or API Gateway / Load balancer (for extra protection) 
    *Request Logging
    *Middleware logs basic request/response info. 
    *Detailed logs (like auth failures, errors) → Logger service in the app. 
    *Monitoring / Metrics
    *Middleware can increment counters (requests, errors). ✅
    *But detailed metrics (latency histograms, DB timings, queue metrics) → Exporter to Prometheus / APM tool. ✅
    *Security checks
    *Middleware is the right spot (auth, CSRF, headers, etc.).
    */


/**
 * get the data and update it to prometheus
 */

/**
 *No need of storing things in mysql instead we can view that in Grafna, so there is no need fetching things db
 */
class requestMonitor {
    private static $prefix = 'CLOUDLINK_GATEWAY_PROMETHEUS_'; 

    public function __construct(?Request $request=NULL) {
        $this->request = $request;

    }
    public function configPromStorage() {
        \Prometheus\Storage\Redis::setDefaultOptions(
        [
            'host' => env("REDIS_HOST"),
            'port' => env("REDIS_PORT"),
            'password' => env("REDIS_PASSWORD"),
            'timeout' => 1, // in seconds
            'read_timeout' => '10', // in seconds
            'persistent_connections' => false
        ]
        );
    }
    public function putHttpReqCount ($ip,string $service,string $path,$code) {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('CloudLink', 'api_gateway_req_count', 'it increases', ['ip','service','path',"status_code"]);
        $counter->incBy(1, ["$ip","$service","$path","$code"]);


    }
    
    public function getReqMetadata() {

    }
    public function getIP(){
        return $this->request->ip();

    }
    /**
     * not in use
     */
 
    public function fetchAllIPStats() {
        $uri = "http://".env('PROMETHEUS_URI')."/";
        $query = "api/v1/query?query=CloudLink_api_gateway_req_count{path='api/user/signup.api.php'}";
        $endpoint = new Client(
            ['base_uri' => $uri.$query,
            'timeout' =>10.0,
        ]);
        $response= $endpoint->request(
            'GET',
        );
        $exist_1 = method_exists($response,'getBody');
        $exist_2 = method_exists($response,'getHeaders');

        if(!$response || !$exist_1|| !$exist_2) {
            // throw new serverExceptionalHandler(ErrorCode:"2003");
            var_dump("soemthing went wrong");

        }
        $body = $response->getBody();
        $body = (string )$body ?? NULL;
        $body = json_decode($body,true);

        $status = $body['status'] ?? NULL;
        $data = $body["data"] ?? NULL;
        if ($status == "success" && $data != NULL){
            var_dump($data["result"]);
        }

    }
   

    /**
     * updates per day to mysql 
     * if the user available udpate else insert
     */
    public function putUserMonitoredData () {


    }
    /**
     * IP blacklist / whitelist
     * GeoIP checks (optional)
     * Rate-limiting breaches
     * Failed auth attempts per IP / user
     * Suspicious patterns (repeated requests to invalid endpoints)
     */

    public function blockIP () {

    }

}
    // $query = "api/v1/query?query=CloudLink_api_gateway_req_count{service='api/user/signup.api.php'}";

    //connect to the redis server   

    //       \Prometheus\Storage\Redis::setDefaultOptions(
    //     [
    //         'host' => env("REDIS_HOST"),
    //         'port' => env("REDIS_PORT"),
    //         'password' => env("REDIS_PASSWORD"),
    //         'timeout' => 1, // in seconds
    //         'read_timeout' => '10', // in seconds
    //         'persistent_connections' => false
    //     ]
    // );
    
    // CollectorRegistry::getDefault()
    // ->getOrRegisterCounter('', 'some_quick_counter', 'just a quick measurement')
        // ->inc();
    //     $registry = \Prometheus\CollectorRegistry::getDefault();

    //     $counter = $registry->getOrRegisterCounter('CloudLink', 'api_gateway', 'it increases', ['type']);
        
    //     $counter->incBy(3, ['blue']);

    //     $gauge = $registry->getOrRegisterGauge('CloudLink', 'api_gateway','it sets', ['type']);
    //     $gauge->set(2.5, ['blue']);

    //     $histogram = $registry->getOrRegisterHistogram('CloudLink', 'api_gateway', 'it observes', ['type'], [0.1, 1, 2, 3.5, 4, 5, 6, 7, 8, 9]);
    //     $histogram->observe(3.5, ['blue']);

    //     $summary = $registry->getOrRegisterSummary('CloudLink', 'api_gateway','it observes a sliding window', ['type'], 84600, [0.01, 0.05, 0.5, 0.95, 0.99]);
    //     $summary->observe(5, ['blue']);
        

    //     $renderer = new RenderTextFormat();
    //     $result = $renderer->render($registry->getMetricFamilySamples());

    //      return response($result, 200)
    //         ->header('Content-Type', RenderTextFormat::MIME_TYPE);
        
    // }
?>