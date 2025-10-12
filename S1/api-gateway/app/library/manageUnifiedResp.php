<?php

namespace App\library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;   
/**
 * functionalities
 * fetch record of 
 */
class manageUnifiedResp
{
    public $headers = [];
    public $body;
    public $image_types = [
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/webp',
        ];
    public $data_types = [
            'application/json',
            'application/ld+json',
            // 'application/xml',
            // 'application/yaml',
            // 'text/yaml',
     ];
    public $response;
    public $promisable_responses;
    public $response_stack = [];
    public $resp_arr = []; //for caching middleware data supplly
    public function __construct ($promisable_responses,Request $request) 
    {  

        $this->request = $request;
        $this->promisable_responses = $promisable_responses;
        // $this->setResponseHeader();
    }

  
    /**
     * Rules or protocls for sending through CloudLink API gateway
     * structured data can only be parsed and send as response ,SO main is JSON
     * accecptables are json ,xml(not often), plain(not often) , html (optional) other are not sendable and unsupportable
     * if we got any unsupported response either drop that or entirely raise 500
     * 
     * some time internal or backend shows app/json as content type but their echo leads to content disruption
     */
    public function unpackPromisables () {
        $count = 0;
        foreach($this->promisable_responses as $key =>$value ){

            $state = $value['state'];
            if($state === "fulfilled") {
                $val = $value['value'];
                $body = $val->getBody()->getContents();
                $contentType = $val->getHeaderLine("Content-Type");

                if(in_array($contentType,$this->data_types) && $this->isJson($body)){
                    $decode2arr = json_decode($body,true);
                    array_push($this->response_stack,$decode2arr);
                    $packed_data = new resp($body,
                        $val->getStatusCode(),
                        [$val,"getHeaderLine"],
                    );
                    // [
                    //     $body,
                    //     $val->getHeaders(),
                    //     $val->getStatusCode(),
                    // ];
                    $this->resp_arr[$this->request->attributes->get("sub_req_paths")[$count]] = $packed_data; 
                } //and some more elif condition for xml,and other types
                else {
                    Log::error("Unexpected Content-Type: {$contentType} at " . __METHOD__);
                    throw new serverExceptionalHandler(ErrorCode: "2000");
                }

            }
            else if ($state == "rejected") {
                $exception = $value['reason'];
                
                if (method_exists($exception,'hasResponse')) {
                    $response = $exception->getResponse();
                    $body  = $response->getBody()->getContents();
                    $contentType = $response->getHeaderLine("Content-Type");
                    
                    if(in_array($contentType,$this->data_types) && $this->isJson($body)){
                        $decode2arr = json_decode($body,true);
                        array_push($this->response_stack,$decode2arr);
                    } //and some more elif condition for xml,and other types
                    else {
                        Log::error("Unexpected Content-Type: {$contentType} at " . __METHOD__);
                        throw new serverExceptionalHandler(ErrorCode: "2000");
                    }

                } else {
                    // Log::error("Unexpected Content-Type: {$contentType} at " . __METHOD__);
                    throw new serverExceptionalHandler(ErrorCode: "2000");
                }

            }
            else {
                    // Log + throw once
                throw new serverExceptionalHandler(ErrorCode: "2000");
            }

            $count++;
        }

    }
    public function packSomeResponseData () {

    }
    /**
     * not in use
     * set response heeader before forwarding to client
     */
    public function setResponseHeader () {
        
        if(gettype($this->response) == "array") {
            return;
        }
        $exist_1 = method_exists($this->response,'getBody');
        $exist_2 = method_exists($this->response,'getHeaders');

        if(!$this->response || !$exist_1|| !$exist_2) {
            throw new serverExceptionalHandler(ErrorCode:"2003");
        }
        $this->body = (string) $this->response->getBody();
        
        $current_headers = $this->response->getHeaders();
        $hop_by_hop_headers = [
                    "Connection",
                    "TE",
                    "Trailer",
                    "Transfer-Encoding",
                    "Upgrade",
                    "Proxy-Authenticate",
                    "Proxy-Authorization",
                     "Content-Length", //if you using echo or anyprint that while sharing exact content len what backend gave ,you will end up in error,
                //  "Content-Type"  here this should be end to end header ,see the request header function
                        ];
        $current_headers_keys = array_keys($current_headers);
        $mutual = array_intersect($hop_by_hop_headers,$current_headers_keys);
        
        foreach ($current_headers as $key => $value) {
            if(in_array($key,$mutual)) {
                continue;
            }
            // echo $key."=>".$value[0]."\n";
            $this->headers[$key] = $value;
        }

    }
    public function sendResponse() {
        $this->request->attributes->set("response_arr",$this->resp_arr);
        return response()->json($this->response_stack);

    }
   
        /**
     * check if the string is json or not  lets say "1{somethin:somehting}"  here 1 present so it not consider as json , so i above added application/json and check the match type
     */
    public function isJson($val) {
     
        json_decode($val);
        if(json_last_error() === JSON_ERROR_NONE) {
            return true;
        }

    }


}
class resp {
    public function __construct ($content,$code,callable $headerLine) {
        $this->content = $content;
        // $this->headers = $headers;
        $this->code = $code;
        $this->headerLine = $headerLine;
    }
    public function getContent() {
        return $this->content;
    }
   
    // public function getHeaders() {
    //     return $this->headers;

    // }
    public function getStatusCode() {
        return $this->code;
    }
    public function getHeaderLine(string $param) {
        return ($this->headerLine)($param);
    }
}
?>