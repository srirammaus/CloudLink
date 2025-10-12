<?php
namespace App\library;

class cache {
    public $APP_PREFIX;

    public function __contruct () {
        $this->APP_PREFIX = env('REDIS_PREFIX',"CloudLink:");
    }
    //both are for hashmap
    public function isCached ($key) { // with prefix

    }

    public function putCache ($key) {

    }
}
?>
