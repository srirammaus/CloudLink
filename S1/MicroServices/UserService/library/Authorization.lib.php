<?php
namespace library;
require_once __DIR__.'/../vendor/autoload.php'; // Assuming Composer is used for dependency management
include_once __DIR__."/ExceptionHandler.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Authorization {
    public $jwt;

    public function __construct () {
        
    }

    function genJWT ($username,$service) {
        // Define your secret key (keep it secure!)
        $services = ["weather","hyperlocalweather","unified_weather"];
        if(!in_array($service,$services)) {
            throw new \clientException(ErrorCode:"1907");
        }
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,1)."/env",[".env"]);
        $dotenv->load();
        $secretKey = $_ENV["AUTH_SECRET_KEY"];

        // 1. Encoding (Generating a JWT)
        $payload = [
            'iss' => 'CloudLink-user-service', // Issuer
            'scope' => $service, // Audience
            'iat' => time(), // Issued At
            'exp' => time() + (60 * 60), // Expiration time (1 hour from now)
            // 'user_id' => 123,
            'username' => $username
        ];

        $this->jwt = JWT::encode($payload, $secretKey, 'HS256'); //singature
        // echo "Generated JWT: " . $jwt . "\n\n";
        

    }
    public function setAuthHeader () {
        header("Authorization: Bearer ".$this->jwt);
    }

}

?>