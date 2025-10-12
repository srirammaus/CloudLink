<?php
/**
 * set of request sent sequentailly and response should be sent cumalivetely, 
 * 200 responsed , data only sent to client other are not
 * sent with a request id or order id 
 * 
 * 
 * bactching or mixed - is comlex we dont need that
 * sequnatial - more latency , but dependent service , need this, like service B need service A. for this we have to use this
 * parallel-  send next,next, at the end wait and uunfiy 
 */
namespace App\Http\Controllers;

use GuzzleHttp\Promise;
use App\library\manageResp;


use Illuminate\Http\Request;

use App\library\authenticate;

use Illuminate\Cache\RedisStore;
use App\library\manageUnifiedResp;
use App\library\requestAggregation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\library\clientException as clientExceptionalHandler;
use App\library\serverException as serverExceptionalHandler; 

class ReqAggregator extends Controller
{
    /**
     * get the list of pahts form the db
     * 
     */
    public function seperate_and_unify(Request $request) {
        try{
            
            $path = $request->attributes->get("__path__");
            $route_name = $request->attributes->get("__service__")."-service";

            $reqAg = new requestAggregation;

            $sub_request_endpoints = $reqAg->setAgURLs($route_name);
            $promisables = [];
            for($i=0;$i<count($sub_request_endpoints);$i++) {
                $promisables = $reqAg->init_req($request,$promisables,$sub_request_endpoints[$i]);
            }   
            $request->attributes->set("sub_req_paths",$reqAg->sub_req_paths);
            //non-blocking
            $responses = Promise\Utils::settle($promisables)->wait();

            //blocking
            // $responses = Promise\Utils::unwrap($promisables);

            $unify_response = new manageUnifiedResp($responses,$request);
            $unify_response->unpackPromisables();
            

            // echo $responses[1]['state'];
            // echo $responses[0]['value'];
        
            return $unify_response->sendResponse();
          
        

        }
        catch(clientExceptionalHandler $e) {
            $resp = $this->err_resp(e:$e);
            $code = $e->getCode();
            return response()->json($resp,$code);
        }
        catch(serverExceptionalHandler $e) {
            $resp = $this->err_resp(e:$e);
            $code = $e->getCode();
            return response()->json($resp,$code);
        }
        catch(\Throwable $e) {
            $resp = $this->err_resp(e:$e);
            $code = 500;
            return response()->json($resp,$code);
        }

        
    }

    /**
     * error response setter
     */
    public function err_resp($message = "Internal Server",$ErrorCode = 2000 , $e =NULL) {
        if($e != NULL) {
            $msg = $e->getMessage();
            if(method_exists($e,'getCustomMessage')) {
                $ErrorCode = $e->getErrorCode();
                $msg = $e->getCustomMessage();
                Log::error(
                    $e->getCustomMessage(),[]
                );
            }else {
                Log::error(
                        $e->getMessage(),[]
                    );
            }

            return [
                "message" => $msg,
                "ErrorCode"=> $ErrorCode,
            ];
        }
        Log::error("Unexpected Error at " . __LINE__);
        return [
            "message" => $message,
            "ErrorCode"=> $ErrorCode,
        ];
    }


}


?>