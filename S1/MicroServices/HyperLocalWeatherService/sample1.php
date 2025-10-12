<?php
// Microservice info
$serviceName = "Service 1";
$port = $_SERVER['SERVER_PORT'];
$host = $_SERVER['HTTP_HOST'];
$time = date("Y-m-d H:i:s");

// Output HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $serviceName; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .info { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h1><?php echo $serviceName; ?></h1>
        <div class="info">
            <strong>Host:</strong> <?php echo $host; ?><br>
            <strong>Port:</strong> <?php echo $port; ?><br>
            <strong>Time:</strong> <?php echo $time; ?><br>
            <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
            <strong>Directory:</strong> <?php echo __DIR__; ?><br>
        </div>
    </div>
</body>
</html>
