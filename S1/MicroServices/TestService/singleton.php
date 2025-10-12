<?php

class Singleton
{
    private static $instance;

    // Private constructor to prevent direct instantiation
    private function __construct()
    {
        // Initialization logic (optional)
        echo "Singleton instance created!" . PHP_EOL;
    }

    // Static method to get the single instance
    public static function getInstance(): Singleton
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prevent cloning the instance
    private function __clone() {}

    // Prevent unserializing the instance
    private function __wakeup() {}

    public function doSomething()
    {
        echo "Doing something with the singleton instance." . PHP_EOL;
    }
}

// Usage:
// $singleton1 = Singleton::getInstance();
// $singleton1->doSomething();

// $singleton2 = Singleton::getInstance();
// $singleton2->doSomething();

// // Verify that both variables hold the same instance
// if ($singleton1 === $singleton2) {
//     echo "Both variables hold the same singleton instance." . PHP_EOL;
// }

?>