<?php

class Container {
    protected $instances = [];

    public function bind($class, $callback) {
        $this->instances[$class] = $callback;
    }

    public function make($class) {
        if (isset($this->instances[$class])) {
            return $this->instances[$class]($this);
        }

        // Use reflection to create the object automatically
        $reflector = new ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return new $class;
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $paramClass = $param->getType()?->getName();
            if ($paramClass) {
                $params[] = $this->make($paramClass); // recursive
            }
        }

        return $reflector->newInstanceArgs($params);
    }
}

class Singleton {
    public function doSomething() {
        echo "Doing something!\n";
    }
}

