<?php

namespace App\library;
use Illuminate\Support\Facades\Log;
use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;   
/**
 * functionalities
 * fetch record of 
 */
class manageResp
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
    public $temp;    
    public function __construct ($response) 
    {  

        $this->response = $response;    
        $this->setResponseHeader();
    }

    public function user () {

    }
    public function unpackResponse () {

    }
    /**
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
    /**
     * dont stuck with domain name mismatch in postman , that cause cookie not reflext in cookie section
     * please here also have a loook in content -length that cause probllm
     * and furture make this if else clause to switch case , because if in this was dynamic , now itself allowed content types are
     * app:-
     * app/json 
     * image:-
     * image/png ,
     * image/jpeg 
     * image/gif ,
     * image/webp ,
     * 
     */
    public function sendResponse() {
        if(gettype($this->response) == "array") {
            return response()->json($this->response);
        }
        
        // Content-Type is always an array, take first value or null // use this later - 
        $contentType = $this->response->getHeaderLine('Content-Type');
        
        if (in_array($contentType,$this->data_types) || $this->isJson($this->body)) { 
            return response($this->body)->withHeaders($this->headers);

        }else if(in_array($contentType,$this->image_types)) {

            return response($this->body)->withHeaders($this->headers);
        }
        else {
                // Log + throw once
            Log::error("Unexpected Content-Type: {$contentType} at " . __METHOD__);
            throw new serverExceptionalHandler(ErrorCode: "2000");
        }
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
?>