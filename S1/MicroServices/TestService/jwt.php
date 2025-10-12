<?php

require 'vendor/autoload.php'; // Assuming Composer is used for dependency management

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define your secret key (keep it secure!)
$secretKey = 'your_super_secret_key_here';

// 1. Encoding (Generating a JWT)
$payload = [
    'iss' => 'your-domain.com', // Issuer
    'aud' => 'your-app-audience', // Audience
    'iat' => time(), // Issued At
    'exp' => time() + (60 * 60), // Expiration time (1 hour from now)
    'user_id' => 123,
    'username' => 'testuser'
];
//for your undersstadning im writing this base64 , dont conduse with other or below . again telling dont confuse with it below .i means
$b64 = base64_encode(json_encode($payload));
echo "\n$b64\n";
//-----------------------------
$jwt = JWT::encode($payload, $secretKey, 'HS256'); //singature
echo "Generated JWT: " . $jwt . "\n\n";

// 2. Decoding and Verifying a JWT
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "Decoded JWT:\n";
    print_r($decoded);
} catch (Exception $e) {
    echo "Error decoding JWT: " . $e->getMessage() . "\n";
}

?>