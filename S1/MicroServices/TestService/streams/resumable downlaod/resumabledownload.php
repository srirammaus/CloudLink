<?php
//Use this with CURL because browser default use this script only once , no range headers work gonna be carried by the browser here
//because this worker thread already running with while loop , while doing pause in frontend whic make this worker request to pause if they resume this
// worker request reusmes then while runniing and the function keep on wokring
//while using curls use with range of bytes , then you will understand the range of workslows
//curl -v -r 0-9999999999999 http://localhost:8084/streams/resumable%20downlaod/resumabledownload.php?file=example.iso -o part2.iso
// Robust file download with Range support (resume)
$baseDir = __DIR__ . '/files/';

if (empty($_GET['file'])) {
    http_response_code(400);
    exit('Missing file parameter.');
}

$filename = basename($_GET['file']);
$path = realpath($baseDir . $filename);

if ($path === false || strpos($path, realpath($baseDir)) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

$filesize = filesize($path);
$mime = mime_content_type($path) ?: 'application/octet-stream';
$fp = fopen($path, 'rb');

if ($fp === false) {
    http_response_code(500);
    exit('Unable to open file.');
}
// Default headers
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . rawurlencode(basename($path)) . '"');
header('Accept-Ranges: bytes');

// Handle HTTP Range requests

$range = null;
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (!empty($headers['Range'])) $range = $headers['Range'];
}

$start = 0;
$end = $filesize - 1;
$httpStatus = 200;

if ($range) {
    // Example header: Range: bytes=500-999
    if (preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
        if ($matches[1] !== '') $start = intval($matches[1]);
        if ($matches[2] !== '') $end = intval($matches[2]);

        // Validate range
        if ($start > $end || $start >= $filesize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes */$filesize");
            fclose($fp);
            exit;
        }
        $httpStatus = 206; // Partial Content
        header("HTTP/1.1 206 Partial Content");
        header("Content-Range: bytes $start-$end/$filesize");
        header("Content-Length: " . ($end - $start + 1));
    }
} else {
    header('Content-Length: ' . $filesize);
}

// Seek to start
//seek vs fgets , by using seek we can fetch randomly or start randmly , fgets sequttially happen
fseek($fp, $start);

// Stream in chunks
$chunkSize = 8192; // 8 KB per loop
while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
    // file_put_contents('debug.txt'," This will once\n", FILE_APPEND);

    $bytesToRead = $chunkSize;
    if ($pos + $bytesToRead > $end + 1) {
        $bytesToRead = $end - $pos + 1;
    }
    $data = fread($fp, $bytesToRead);
    echo $data;
    flush();
    if (connection_status() !== CONNECTION_NORMAL) {
        break; // client aborted
    }
}

fclose($fp);
file_put_contents('debug.txt', date('H:i:s') . " ended happen only once\n", FILE_APPEND);

exit;


// Sure! Let’s go step by step and explain what this PHP script does. This is a robust file download script with support for resumable downloads (HTTP Range).

// 1. Setup Base Directory
// $baseDir = __DIR__ . '/files/';


// All downloadable files are stored in the /files/ directory relative to this script.

// __DIR__ is the current directory of this script.

// 2. Check if file parameter exists
// if (empty($_GET['file'])) {
//     http_response_code(400);
//     exit('Missing file parameter.');
// }


// Expects a file parameter in the URL, e.g., download.php?file=example.pdf.

// If missing → returns HTTP 400 Bad Request.

// 3. Validate and Sanitize File
// $filename = basename($_GET['file']);
// $path = realpath($baseDir . $filename);


// basename() ensures no directory traversal (../../../secret.txt).

// realpath() resolves the actual absolute path.

// if ($path === false || strpos($path, realpath($baseDir)) !== 0 || !is_file($path)) {
//     http_response_code(404);
//     exit('File not found.');
// }


// Ensures file exists and is inside $baseDir.

// Prevents users from accessing files outside the intended directory.

// 4. Open File and Get Info
// $filesize = filesize($path);
// $mime = mime_content_type($path) ?: 'application/octet-stream';
// $fp = fopen($path, 'rb');


// $filesize → total size of the file in bytes.

// $mime → file MIME type (default to binary if unknown).

// fopen() → open file in binary read mode (rb).

// if ($fp === false) {
//     http_response_code(500);
//     exit('Unable to open file.');
// }


// Checks if file can actually be opened.

// 5. Set Default Headers
// header('Content-Type: ' . $mime);
// header('Content-Disposition: attachment; filename="' . rawurlencode(basename($path)) . '"');
// header('Accept-Ranges: bytes');


// Content-Type → tells the browser the file type.

// Content-Disposition: attachment → forces download with the file name.

// Accept-Ranges: bytes → tells browser resumable download is supported.

// 6. Handle HTTP Range Requests
// $range = null;
// if (isset($_SERVER['HTTP_RANGE'])) {
//     $range = $_SERVER['HTTP_RANGE'];
// } elseif (function_exists('apache_request_headers')) {
//     $headers = apache_request_headers();
//     if (!empty($headers['Range'])) $range = $headers['Range'];
// }


// Checks if the client requested a partial download using Range header.

// Example: Range: bytes=500-999 → download only bytes 500 to 999.

// 7. Parse Range
// $start = 0;
// $end = $filesize - 1;
// $httpStatus = 200;


// By default, download the whole file.

// if ($range) {
//     if (preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
//         if ($matches[1] !== '') $start = intval($matches[1]);
//         if ($matches[2] !== '') $end = intval($matches[2]);

//         // Validate range
//         if ($start > $end || $start >= $filesize) {
//             header('HTTP/1.1 416 Requested Range Not Satisfiable');
//             header("Content-Range: bytes */$filesize");
//             fclose($fp);
//             exit;
//         }
//         $httpStatus = 206; // Partial Content
//         header("HTTP/1.1 206 Partial Content");
//         header("Content-Range: bytes $start-$end/$filesize");
//         header("Content-Length: " . ($end - $start + 1));
//     }
// } else {
//     header('Content-Length: ' . $filesize);
// }


// Partial download → returns 206 Partial Content and sets Content-Range.

// Validates the requested range.

// If invalid → returns 416 Requested Range Not Satisfiable.

// 8. Start Streaming the File
// fseek($fp, $start); // Move pointer to the starting byte


// Moves the file pointer to $start position.

// $chunkSize = 8192; // 8 KB
// while (!feof($fp) && ($pos = ftell($fp)) <= $end) {
//     $bytesToRead = $chunkSize;
//     if ($pos + $bytesToRead > $end + 1) {
//         $bytesToRead = $end - $pos + 1;
//     }
//     $data = fread($fp, $bytesToRead);
//     echo $data;
//     flush();
//     if (connection_status() !== CONNECTION_NORMAL) {
//         break; // client aborted
//     }
// }


// Reads the file in chunks (8 KB per loop) → memory efficient.

// Stops if:

// End of file reached.

// Requested range is completed.

// Client disconnected.

// 9. Cleanup
// fclose($fp);
// exit;


// Closes file handle and ends the script.

// ✅ Key Features of This Script

// Resumable downloads using HTTP Range headers.

// Chunked file streaming → avoids memory overload for large files.

// Security checks → prevents directory traversal.

// Handles partial content (206) and invalid ranges (416).

// Auto-detects MIME type and sends correct headers.


// 🧱 1️⃣ What You Have (and Why It’s Already Good)

// Your current script:

// ✅ Supports HTTP Range requests (core for resumable downloads & streaming)
// ✅ Uses fread() + flush() for chunked output (no memory explosion)
// ✅ Validates file existence and access scope (safe basename() and realpath())
// ✅ Sends correct headers (Content-Type, Accept-Ranges, Content-Range, etc.)
// ✅ Gracefully handles client disconnects (connection_status())
// ✅ Works for both browsers and download clients (e.g., curl, wget, IDM, etc.)

// That’s already 95% what real systems do when serving static content dynamically.

// 🧰 2️⃣ Where the “Industry” Adds Extra Layers

// Big production systems add optimizations or protections around this same logic. Let’s list them clearly:

// Category	Industry Enhancement	Why
// Buffering	Disable PHP output buffering and compression (ini_set('zlib.output_compression', 0))	Prevent PHP from altering byte offsets or delaying chunks
// Headers	Send Cache-Control, ETag, and Last-Modified headers	Let browsers cache partial content properly and resume faster
// Memory & Execution	Use @set_time_limit(0) and ignore_user_abort(true)	Prevent long-running file reads from being interrupted
// MIME Detection	Hardcode MIME for known types instead of using mime_content_type() every time	Faster, avoids edge-case misdetections
// Logging	Add server-side logging (file start, range, end, bytes sent)	Monitor usage and debug interrupted downloads
// Speed Throttling	Optionally limit transfer rate (usleep())	Simulate or control bandwidth in production
// Security	Verify authorization before sending file	Prevent unauthorized direct file access
// Scalability	Offload to web server (Apache mod_xsendfile, Nginx X-Accel-Redirect)	PHP tells the web server “you send this file” for efficiency

// Example of offloading (real industry pattern):

// // After validating permissions:
// header("X-Sendfile: $path");
// header('Content-Type: application/octet-stream');
// header('Content-Disposition: attachment; filename="' . basename($path) . '"');
// exit;


// This tells Apache or Nginx to send the file directly — zero PHP load, used in production CDNs, video platforms, and download services.

// 🧩 3️⃣ Why Companies Still Use PHP Download Scripts

// Even in 2025, many platforms (internal tools, CMSs, SaaS) still use PHP scripts like yours because:

// They allow fine-grained access control (check user session, file ownership).

// They support logging and billing (track which user downloaded what, how many bytes).

// They can inject security or watermark headers dynamically.

// And they integrate cleanly with existing PHP ecosystems (e.g., Laravel, WordPress).

// So — it’s not outdated or “hobby code”. It’s practical, reliable, and secure when hardened.



?>
