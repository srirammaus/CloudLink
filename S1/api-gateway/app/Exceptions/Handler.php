<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    //custom
    public $response = [
        "message" => "something wentt wrong",
        "ErrorCode" =>"2000"
    ];
    
    protected $code = 500; //default errr code
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    // App\Exceptions\Handler.php
    public function report(Throwable $exception)
    {
        if ($exception instanceof CustomException) {
            // Log this specific exception differently
            Log::channel('custom_log')->error('Custom exception occurred: ' . $exception->getMessage());
        }
        // $this->response = json_encode($this->response);

        parent::report($exception);
        // return response($response,500);

    }
    // App\Exceptions\Handler.php
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        if ($request->expectsJson() && $exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $exception->errors(),
            ], 422);
        }
        Log::error("Error Message:  ".$exception->getMessage(). " Error Line:  ".$exception->getLine() ." Error File :  ".$exception->getFile());
        $this->response = json_encode($this->response);
        return response($this->response,500);
        // return parent::render($request, $exception);
    }
}
