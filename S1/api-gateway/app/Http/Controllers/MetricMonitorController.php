<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\library\requestMonitor;

use Prometheus\RenderTextFormat;
use Prometheus\CollectorRegistry;
use Illuminate\Support\Facades\Log;

class MetricMonitorController extends Controller
{
    //
    public function __construct () {

    }
    public function updateMetrics () {

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

        $registry = \Prometheus\CollectorRegistry::getDefault();


        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());
        Log::error("res: " .$result);
        return response($result, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
        
    }
}

/**getOrRegisterCounter(namespace, name, help, labels)
Parameter	Type	Meaning	Example / Notes
namespace	string	A prefix for the metric, used to group metrics logically.	'test', 'app', 'http'
name	string	The metric name itself.	'some_counter', 'request_count'
help	string	A description of what this metric measures. Shows up in /metrics.	'it increases', 'Total API requests'
labels	array	Optional labels (dimensions) for this metric. These are like columns in SQL that differentiate values.	['type'], ['method', 'status'] */
