<?php

require __DIR__ . "/singleton.php";

class Temp
{
    public function test(Singleton $single)
    {
        $single->doSomething();
    }
}

// Example usage
$t = new Temp();

// Pass the singleton instance to test()
$t->test(Singleton::getInstance());
$t->test(Singleton::getInstance());
