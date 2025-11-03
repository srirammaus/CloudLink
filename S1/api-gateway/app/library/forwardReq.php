<?php

namespace App\library;


use GuzzleHttp\Client;
use App\Models\EndpointData;

use App\library\authenticate;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Exception\ClientException;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;   
/**
 * functionalities
 * fetch record of 
 */
class forwardReq
{
    protected $method;
    protected $route_name;
    protected $body;
    protected $query;
    protected $dr_content; //it means it denotes both body and query or anything added in future // in guzzle all parameter format are like same [....]
    protected $url;
    protected $path = "/";
    protected $uri = "http://localhost:8081";  //fallback
    protected $params = [];
    protected $headers = [];
    protected $cookies = [];
    protected $session = [];
    protected $localstorage =[];
    protected $auth;
    protected $requestID;

    public function __construct () {
        
    }
    /**
     * used to initate the req to respected services
     */
    public function init_req ($request) {
        try {
            $this->method = $request->method();
            $this->path = "/".$request->path();
            $this->body = $request->all();

            $this->setRequestBody($request);
            $this->genReqID();
            $this->setRequestHeaders();

            $this->fetch_data();
            // fetch the url actuall response from the endpoint

            //check for authentication 
            if($this->auth == '1') {

                if(authenticate::isAuthenticated($this->headers)) { //let the user in if only authenticated                    
                    $endpoint = new Client([
                        "base_uri" => "http://".$this->uri,
                        "timeout"  => 10.0,
                        "cookies"  => true,
                    ]);
                   
                    $resp = $endpoint->request(
                        $this->method,
                        $this->path,
                        $this->dr_content,
                    );
                } else {
                    //fallback
                    throw new serverExceptionalHandler(ErrorCode:"2003",code:500);
                }
            }else {
                $endpoint = new Client([
                    "base_uri" => "http://".$this->uri,
                    "timeout"  => 10.0,
                    "cookies"  => true,
                ]);
                $resp = $endpoint->request(
                    $this->method,
                    $this->path,
                    $this->dr_content,
                );
            }
            return $resp;  // here where response manager works takes place

        }catch (ClientException $e) { 

            $gz = $this->guzzleErrorResp($e);
            if(!$gz){ //
                throw new clientExceptionalHandler(ErrorCode:"2000");
            }
            else {
                return $gz;
            }
        }catch (ServerException $e) {
            $gz = $this->guzzleErrorResp($e);
            if(!$gz){
                throw new serverExceptionalHandler(ErrorCode:"2000");
            }
            else {
                return $gz;
            }
        }catch (RequestException $e) {
            
            $gz = $this->guzzleErrorResp($e);
            if(!$gz){
                throw new serverExceptionalHandler(ErrorCode:"2000");
                
            }else {
                return $gz;
            }
            
        }catch (\Throwable $e) {
            
            $gz = $this->guzzleErrorResp($e);
            if(!$gz){ 
                if($e instanceof serverExceptionalHandler) {
                    throw new serverExceptionalHandler(ErrorCode:$e->getErrorCode(),code:$e->getCode());
                }else if ($e instanceof clientExceptionalHandler) {
                    
                    throw new clientExceptionalHandler(ErrorCode:$e->getErrorCode(),code:$e->getCode());
                }else {   
                    Log::error(
                        $e->getMessage(),[
                        "line"=>$e->getLine(),
                        ]
                    );
                    throw new serverExceptionalHandler(ErrorCode:"2000");
                }
            }else {
                return $gz;
            }
        }

    }
    public function fetch_data() {
        //first fetch the endpoint data from the db
        $endpoint_data = EndpointData::where("path",$this->path)->first();
        if($endpoint_data){
            $this->url = $endpoint_data->baseURL;
            $this->route_name = $endpoint_data->name;
            $this->path = $endpoint_data->path;
            $this->path = implode("/",array_slice(explode("/",$this->path),3));
            $this->port = $endpoint_data->port;
            //join the url
            $this->uri = $this->url.":".$this->port;
            $this->auth = $endpoint_data->auth; //1 or 0
        }else {
            throw new clientExceptionalHandler(ErrorCode:"2701",code:404);
        }
    }
    /**
     * Essentails headers and custom headers has to sent along with the request
     */
     //array_diff(return an array that contains the entries from array1 that are not present in array2 or array3, etc.)
    public function setRequestHeaders() {
        $current_headers = getAllheaders();
        //Most blog post not saying content length is hop by hop because RFC not mentioned but , content length is also hop by hop (while sedning request) because it cant
        //send directly, proxy has to rearrange or recalultes the formdata or multipart in here (api gateway)then the content length is automtically generated 

        $hop_by_hop_headers = [
                "Connection",
                 "TE",
                 "Trailer", 
                 "Transfer-Encoding",
                  "Upgrade",
                 "Proxy-Authenticate",
                  "Proxy-Authorization",
                 "Content-Length",
                 "Content-Type"
                        ];
        $current_headers_keys = array_keys($current_headers);
        $mutual = array_intersect($hop_by_hop_headers,$current_headers_keys);
                        
        $this->headers = [
            // "headers"=>[

                // ...essential headers from $current_headers,hop-by-hop headers are not fowarded difrectly , only end to end header only forwareded directly
                //which put here by looping

                //proxy or our api headers (non standard)
                "Api-Gateway-Auth"=> env("API_GATEWAY_KEY"),
                'X-Forwarded-For' => $_SERVER["REMOTE_ADDR"], //client_ip
                'X-Forwarded-Port'=> $_SERVER["REMOTE_PORT"],
                'X-Forwarded-Host'=> $_SERVER["REMOTE_HOST"] ?? NULL,
                'X-Forwarded-Path'=> $this->path,
                'X-Forwarded-Method'=> $this->method,
                'X-Forwarded-Url' => $this->url,
                'X-Forwarded-By'=> 'CloudLink-api-gateway',
                'X-Forwarded-Name'=> $this->route_name,
                'CloudLink-Request-Id' => $this->requestID, //for tracing the request
            // ]
        ];
        foreach ($current_headers as $key => $value) {
            if(in_array($key,$mutual)) {
                continue;
            }
            $this->headers[$key] = $value;
        }
        $this->dr_content["headers"] = $this->headers;

    }
    public function setRequestBody ($request) {
        //check the content type if matched moved further
        $this->body = $request->all();  //x-www-form-urlendoded - getContent()

        $type = $request->header('Content-type');
        $type = explode(";",$type)[0];
        // echo $type;
        $text_types = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'text/csv',
            'text/xml',
        ]; 

        // 2. Form Submission
        $form_types = [
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ];

        // 3. JSON / XML / YAML
        $data_types = [
            'application/json',
            'application/ld+json',
            'application/xml',
            'application/yaml',
            'text/yaml',
        ];

        // 4. Binary / Streams
        $binary_types = [
            'application/octet-stream',
            'application/pdf',
            'application/zip',
            'application/gzip',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        // 5. Images
        $image_types = [
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/webp',
        ];

        // 6. Audio
        $audio_types = [
            'audio/mpeg',
            'audio/ogg',
        ];

        // 7. Video
        $video_types = [
            'video/mp4',
            'video/webm',
        ];

        // 8. Other API-related
        $other_types = [
            'application/graphql',
            'application/x-ndjson',
            'application/x-protobuf',
            'application/x-msgpack',
            'multipart/mixed',
        ];
        if($this->method == "GET" || $this->method == "get") {
            //no body , so use query
            
            $this->query = [
                    'query' =>[]
                ];
            foreach ($this->body as $key => $value) {
                $this->query['query'][$key] = $value;
            }
            $this->dr_content = $this->query;

        }else {
            switch ($type) {
                case 'application/x-www-form-urlencoded':
                    # code...
                    break;
                case 'multipart/form-data';
                    $this->multipart = [
                        "multipart"=>[],
                    ];
                    foreach ($this->body as $key => $value) {
                        // echo $key. " => " .$value;
                        $data = [
                            'name' => $key,
                            'contents' => $value,
                        ];
                        array_push($this->multipart["multipart"],$data);
                    }
                    $this->dr_content = $this->multipart;
                    break;
                case 'application/json':
                    break;
                case 'application/xml':
                    break;
                default:  // add octect stream and make video upload //very important noticed by recuirters
                    # code...
                    break;
            }
        }

    }

    public function genReqID() {
        $id = random_bytes(8);
        $id2= bin2hex($id);
        $id3 = rand(4444,55555);
        $this->requestID = "REQUESTID.".$id2.$id3;

    }
    public function guzzleErrorResp ($e) {
        $responser = method_exists($e,'hasResponse');
        if($responser != false && $e->hasResponse()){
            $resp = $e->getResponse();   //need this compulsory
            // $resp = $resp->getBody();this work carried byh response manageer
            return $resp;
        }
    }
   
    
}
