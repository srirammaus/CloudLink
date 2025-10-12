<?php
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir);

$fileName = $_POST['fileName'];
$chunkIndex = (int)$_POST['chunkIndex'];
$totalChunks = (int)$_POST['totalChunks'];

$tmpFile = $_FILES['chunk']['tmp_name']; // tmp name came from php itslef created to each file
$destFile = $uploadDir . $fileName;

// append the chunk using fopen
$out = fopen($destFile, $chunkIndex === 0 ? 'wb' : 'ab');
$in = fopen($tmpFile, 'rb');

while ($buff = fread($in, 8192)) { // yoou can alssouse stream get content instead , but dont use fgets becasue it get line by line
    fwrite($out, $buff);
}
fclose($in);
fclose($out);

if ($chunkIndex + 1 === $totalChunks) {
    echo "✅ Upload complete: {$fileName}";
} else {
    echo "Chunk {$chunkIndex} / {$totalChunks} received.";
}
