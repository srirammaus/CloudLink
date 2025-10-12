<?php

namespace App\Http\Controllers;

use App\library\forwardReq;
use App\library\authenticate;

use App\library\manageResp;
use App\library\monitor;
use Illuminate\Http\Request;

use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Redis;
use App\library\serverException as serverExceptionalHandler; 
use App\library\clientException as clientExceptionalHandler;   

class serviceController extends Controller
{
    /**
     * functionalities , get the URL from the db
     * must set the timeout for every request
     * 
     */
    public function __construct () {

    }
    /**
     * Add response for text/html, img ,video, raw bytes(upload), becasue you may send the captcha image through it
     */
    public function ReqRoute(Request $request) {
        

        try{
            
            $forwardReq   = new forwardReq;
            $response  = $forwardReq->init_req($request);

            //get the response body

            $manageResp =  new manageResp($response);     
            
            return $manageResp->sendResponse();
            


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
