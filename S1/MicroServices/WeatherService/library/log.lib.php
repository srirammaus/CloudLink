<?php 
namespace library;
/**
 * This file is used to log wherever the exception or Error it should log it and store it
 * @param message
 * @param  file - example database_err.log
 * 
 * for errors - get $e from the using function
 * for others like backend log - just get the message
 * logg() should only in the  catching area not in the throing sending area
 * but you place it in set_execption_handler place becuas the error came to there becuase it is uncaught
 * 
 * 
 * loggin format
 * {
*   "timestamp": "2025-08-25 19:30:12",
*   "level": "ERROR",
*   "code": 1001,
*   "userId": 42,
*   "message": "Invalid email format",
*   "context": {
*   "input": "abc@@xyz.com"
  *}
*}

*/
function logg($file,$message=null,$exception_=null,$type = "err"){ // this type err is used for future, if we getting postive logs we can this as 
    date_default_timezone_set("Asia/Kolkata");
    $root_dir = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname(__DIR__, 1);

    if($exception_) { 
        $msg = method_exists($exception_,'getCustomMessage')?$exception_->getCustomMessage():$exception_->getMessage();
        $exception_ = array(
            "line"=>$exception_->getLine(),
            "code"=>$exception_->getCode(),
            "time"=>date("Y-m-d  h:i:sa"),
            "message"=>$msg);
        $exception_ = json_encode($exception_);
        
    }else {
        $exception_ = $message;
    }
    
    switch ($file) {
        case 'db_err':
            $file = fopen("$root_dir"."/error_log/db_err.log","a+");
            fwrite($file,$exception_."\n\n\n");
            break;
        case 'server_err':
            $file = fopen("$root_dir"."/error_log/server_err.log","a+");
            fwrite($file,$exception_."\n\n\n");
            break;
        case 'client_err': 
            $file = fopen("$root_dir"."/error_log/client_err.log","a+");
            fwrite($file,$exception_."\n\n\n");
            break;
        case 'redis_err': 
            $file = fopen("$root_dir"."/error_log/redis_err.log","a+");
            fwrite($file,$exception_."\n\n\n");
            break;
        case "backend_log":
            $file = fopen("$root_dir"."/error_log/backend_.log","a+");
            fwrite($file,$message."\n");
            break;
        default:
            $file = fopen("$root_dir"."/error_log/server_err.log","a+");
            fwrite($file,$exception_."\n\n\n");
            break;
    }

}

?>