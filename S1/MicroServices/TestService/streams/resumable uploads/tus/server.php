<?php
require __DIR__.'/../../../vendor/autoload.php';

$server = new \TusPhp\Tus\Server('file');  // you can pass “redis”, “apcu”, or “file” as storage
$server->setUploadDir(__DIR__ . '/uploads');

// You can hook into events
$server->event()->addListener('tus-server.upload.complete', function (\TusPhp\Events\TusEvent $event) {
    $file = $event->getFile();
    // $file->getFilePath(), $file->details() etc.
    // Do something after upload completes, e.g. move, record, notify, etc.
});

$response = $server->serve();
$response->send();
exit(0);


// <?php  This much configuration bellow we can add
// require 'vendor/autoload.php';

// use TusPhp\Tus\Server;

// $server = new Server('file');
// $server->setUploadDir(__DIR__ . '/uploads');
// $server->setCacheDir(__DIR__ . '/cache');
// $server->setApiPath('/files');

// // Event hooks
// $server->event()->addListener('tus-server.upload.created', fn($e) =>
//     error_log('Created upload: ' . $e->getFile()->getName())
// );

// $server->event()->addListener('tus-server.upload.updated', fn($e) =>
//     error_log('Uploaded chunk: ' . $e->getFile()->getOffset() . ' bytes')
// );

// $server->event()->addListener('tus-server.upload.complete', fn($e) => {
//     $file = $e->getFile();
//     rename($file->getFilePath(), __DIR__ . '/final/' . $file->getName());
// });

// $response = $server->serve();
// $response->send();
// exit;

?>

