<?php
// config
$dbHost = 'localhost';
$dbName = 'location';
$dbUser = 'root';
$dbPass = '';
$port = 3310;
$table  = 'cities_db'; // table with `city` and `state` columns

// connect to DB
$pdo = new PDO("mysql:host=$dbHost;port=$port;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// fetch cities without state
$stmt = $pdo->query("SELECT city FROM $table WHERE state IS NULL OR state=''");
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cities as $row) {
    // $id = $row['id'];
    $city_ = $row["city"];
    $city = urlencode($city_);
    
    // OpenStreetMap Nominatim API
    $url = "https://nominatim.openstreetmap.org/search?city=$city&country=India&format=json&addressdetails=1&limit=1";
    
    $opts = [
        "http" => [
            "header" => "User-Agent: MyCityScript/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    
    $response = file_get_contents($url, false, $context);
    if ($response === FALSE) {
        echo "Error fetching $city\n";
        continue;
    }
    /**India → State
    France → Region / Département
    Japan → Prefecture
    Kuwait → Governorate
    Others → Province, County, District, etc.*/
    $data = json_decode($response, true);
    if (isset($data[0]['address']['state'])) {
        $state = $data[0]['address']['state'];
        
        // update DB
        $update = $pdo->prepare("UPDATE $table SET state=:state WHERE city=:city");
        $update->execute([':state' => $state, ':city' => $city_]);
        
        echo "Updated $city → $state\n";
    } else {
        echo "No state found for $city\n";
    }
    
    // respect rate limits
    sleep(1); // Nominatim: max 1 request per second
}

echo "Batch process completed.\n";
