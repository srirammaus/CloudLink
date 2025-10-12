<?php
// Simple secure file download using readfile()
// Place this file in your web root and set $baseDir to where downloads live.

$baseDir = __DIR__ . '/files/'; // directory containing allowed downloads

// Get requested file name (e.g. ?file=example.pdf)
if (empty($_GET['file'])) {
    http_response_code(400);
    exit('Missing file parameter.');
}

$filename = basename($_GET['file']);        // prevents directory traversal like ../../
$path = realpath($baseDir . $filename);

// Ensure file exists and is inside $baseDir
if ($path === false || strpos($path, realpath($baseDir)) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

// Optional: check allowed extensions or a whitelist
$allowedExt = ['pdf','zip','png','jpg','mp4',"exe","msi","iso"];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(403);
    exit('Forbidden file type.');
}

// Send headers
header('Content-Description: File Transfer');
header('Content-Type: ' . mime_content_type($path));
header('Content-Disposition: attachment; filename="' . rawurlencode(basename($path)) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=0');
header('Pragma: public');

// Stream file
readfile($path);
exit;
?>