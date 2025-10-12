<?php
/**
 * Why this libraby , this needs to  included in every api, simply we could say this like a abstract class but not .
 * 
 */ 

namespace RESTapi;
include_once "library/ExceptionHandler.php";
include_once "library/log.lib.php";
include_once "config/db_conn.php";
require_once __DIR__ . '/vendor/autoload.php';

use function \library\logg ;
class REST {
    public $content_type = "application/json";
    private $_code;
    private $_message;

    public $_request = array();
    public function __construct () {
        $this->setXResponseHeader();
        $this->sanitization();
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__."/env",[".env"]);
        $dotenv->load();
        /**
         * either gateway authorized or the user should be authozied
         */
        $user_auth = $this->isAuthorizedUser();
        $gateway_auth = $this->isGatewayAuthorized();
        if(!$gateway_auth &&  !$user_auth){
            $message = "Unauthorized access";
            $this->response(401,"1700",$message);
            exit();
        }
    }
    public function isGatewayAuthorized() {
        $header = getallheaders();
        // echo dirname( __DIR__);
        $api_gateway_auth_header = $header["Api-Gateway-Auth"] ?? NULL;
        if($api_gateway_auth_header != NULL) {
            $api_gateway_auth =  $_ENV["API_GATEWAY_KEY"];
            // echo $api_gateway_auth;
            if($api_gateway_auth_header === $api_gateway_auth) {
                // echo "Auth suceesss";
                return true;     
            }
        }
        return FALSE;
            
    }
    public function isAuthorizedUser () { 
        $header = getallheaders();
        $secretKey = $_ENV["AUTH_SECRET_KEY"];
        
   
        $Authorization_token = $header["Authorization"] ?? NULL ;
        if($Authorization_token != NULL) {
            $Authorization_token = explode(" ",$Authorization_token);
            $schema = $Authorization_token[0] ?? NULL;
            $client_sign = $Authorization_token[1] ?? NULL;

            try {
                $decoded = JWT::decode($client_sign, new Key($secretKey, 'HS256'));
                return $decoded;
            } catch (\Exception $e) {
                logg(file:"server_err", exception_:$e);
                // throw new \clientException(ErrorCode:"1700");
                return false;
            }
        }
        return false;
    }
    public function getRequestMethod () {
        // $this->isGatewayAuthorized();
        return $_SERVER["REQUEST_METHOD"];
    }
    /**
     * getting the request header
     */
    public function validateRequestHeader () {
        getallheaders() ;// this is array type , future use it and make a sanitization
    }
    /**
     * sanitize email, phone number , white spaces in all strings except bio (but limited)
     */
 

    public function filterInputs(){
        switch($this->getRequestMethod()){
            case "POST":
                //$this->_request = $this->cleanInputs($_POST);
                $this->_request =  $this->cleanInputs(array_merge($_GET,$_POST));
                break;
            case "GET":
                $this->_request = $this->cleanInputs($_GET);
            case "DELETE":
                $this->_request = $this->cleanInputs($_GET);
                break;
            case "PUT":
                parse_str(file_get_contents("php://input"),$this->_request);
                $this->_request = $this->cleanInputs($this->_request);
                break;
            default:
                $this->response('',406);
                break;
        }

    }

    public function cleanInputs($data){
        $clean_input = array();
        if(is_array($data)){
            foreach($data as $k => $v){
                $clean_input[$k] = $this->cleanInputs($v);
            }
        }else{
            $data = strip_tags($data);
            $clean_input = trim($data);

        }
        return $clean_input;
      
    }

    public function sanitization() {
        /**
         * validateRequestHeader
         * filterInputs
         */
        $this->filterInputs();

    }
    public function getStatusMessage () {
                $status = array(
                    100 => 'Continue',
                    101 => 'Switching Protocols',
                    200 => 'OK',
                    201 => 'Created',
                    202 => 'Accepted',
                    203 => 'Non-Authoritative Information',
                    204 => 'No Content',
                    205 => 'Reset Content',
                    206 => 'Partial Content',
                    300 => 'Multiple Choices',
                    301 => 'Moved Permanently',
                    302 => 'Found',
                    303 => 'See Other',
                    304 => 'Not Modified',
                    305 => 'Use Proxy',
                    306 => '(Unused)',
                    307 => 'Temporary Redirect',
                    400 => 'Bad Request',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    405 => 'Method Not Allowed',
                    406 => 'Not Acceptable',
                    407 => 'Proxy Authentication Required',
                    408 => 'Request Timeout',
                    409 => 'Conflict',
                    410 => 'Gone',
                    411 => 'Length Required',
                    412 => 'Precondition Failed',
                    413 => 'Request Entity Too Large',
                    414 => 'Request-URI Too Long',
                    415 => 'Unsupported Media Type',
                    416 => 'Requested Range Not Satisfiable',
                    417 => 'Expectation Failed',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => 'Bad Gateway',
                    503 => 'Service Unavailable',
                    504 => 'Gateway Timeout',
                    505 => 'HTTP Version Not Supported');
        return ($status[$this->_code])?$status[$this->_code]:$status[500];
    }
    /**
     * setting allow origin 
     * set status code
     * set message
     * set request content type
     */
    public function setResponseHeader() {
        header("HTTP/1.1 ".$this->_code." ".$this->_message);
        header("Content-Type:".$this->content_type);
        header("Access-Control-Allow-origin:http://localhost/");
    }
    public function setXResponseHeader ($X_Cache_Control="None",$X_TTL=0) {
        /**
         * i Put setXResponseHeader in the constucotr function becasue , while we sending the succesfull resposne through response function in this RESTapi, and we set the setxreposnseheader in this response function i might not allow to override the setxresponseheaer if i put contrucor i can override
         * setting custom proxy headers
         * setting header for cache-control of reponses in API gateway
         * @param X-Cache-Control  -[Set,Del,None,Update]
         * @param X-Cache-TTL - [seconds,0]
         */
        header("X-Cache-Control:".$X_Cache_Control);
        header("X-Cache-TTL:".$X_TTL);
    }
    /**
     * A flag may be either a success code(1) or Error Code(any)
     */
    public function response($statusCode,$flag,$message) {
        $this->_code = $statusCode; ///i.e 500,200
        $this->_message = $this->getStatusMessage();
        $this->setResponseHeader();

        $key = $flag > 100? "ErrorCode":"flag";
        $resp = array (
            $key=> $flag,
            "message"=> $message,
        );
       
        echo json_encode($resp);
    }
    /**
     *  invlaid 
     */
    public function badRequest() {
        
    }
    public function test () {
        echo "Works fine..";
    }
    public function JsonPacking () {

    }


    
}
?>