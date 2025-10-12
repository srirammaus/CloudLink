<?php

// require __DIR__."/singleton.php";
require __DIR__."/container.php";



 
class Temp {
    public function test(Singleton $single) {
        $single->doSomething();
    }
}

$container = new Container();
$temp = new Temp();


// Laravel-like magic:
$reflect = new ReflectionMethod($temp, 'test');
$params = [];

foreach ($reflect->getParameters() as $param) {
    $paramClass = $param->getType()?->getName();
    if ($paramClass) {
        $params[] = $container->make($paramClass);
    }
}

// Auto-inject dependencies!
$reflect->invokeArgs($temp, $params);

?>