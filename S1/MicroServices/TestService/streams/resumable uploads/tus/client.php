<?php
// require 'vendor/autoload.php';

// // The base URL where your TUS server is listening
// $baseUrl = 'https://your-domain.com/files';  
// $client = new \TusPhp\Tus\Client($baseUrl);

// // Use a unique key for this upload (for resuming)
// $key = 'my-unique-upload-key';
// $client->setKey($key);

// // Point to a local file path to upload (or a temp file location)
// $client->file('', 'file.bin');

// // Optional: Upload in chunked increments (for resuming)
// $chunkSize = 1 * 1024 * 1024; // 1 MB
// $bytesUploaded = $client->upload($chunkSize);

// // Or upload whole file (no length passed):
// // $client->file('/path/to/local/file.bin', 'file.bin')->upload();

// // You can also get offset:
// $offset = $client->getOffset();  // returns bytes uploaded so far or false if none

// // To delete this upload (abort):
// // $client->delete($key);

?>
