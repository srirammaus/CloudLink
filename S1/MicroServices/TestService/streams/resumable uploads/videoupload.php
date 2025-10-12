<?php
/**
 * completey based on stream
 */
$target_dir = __DIR__."/uploads/"; // Directory where uploaded files will be saved
$target_file = $target_dir . basename($_POST["fileName"]);
// echo $_POST["fileName"];
echo $_FILES["chunk"]["tmp_name"];
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = filesize($_FILES["chunk"]["tmp_name"]);
    if($check !== false) {
        // echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}

// Check if file already exists
// if (file_exists($target_file)) {
//     echo "Sorry, file already exists.";
//     $uploadOk = 0;
// }

// Check file size (e.g., limit to 5MB)
if ($_FILES["chunk"]["size"] > (1024 * 1024)) { //5mb = 5000000 //im here using 5 bytes for testt
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
// if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
// && $imageFileType != "gif"  && $imageFileType != "pdf") {
//     echo "Sorry, only JPG, JPEG, PNG & GIF files are allowede".$imageFileType;
//     $uploadOk = 0;
// }

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    $dir = __DIR__."/uploads";
    if(!is_dir($dir)) mkdir($dir);

    $totalChunks = $_POST["totalChunks"];
    $filename = $_POST["fileName"];
    $chunkIndex = $_POST["chunkIndex"];

    $tmp_data = $_FILES['chunk']["tmp_name"];
    
    $tmp_file = fopen($tmp_data,"rb");
    $upload_file = fopen($dir."/".$filename, $chunkIndex == 0 ? "wb" : "ab");

    while($buff = fread($tmp_file,8000)) {
        //write it or put it 
        fwrite($upload_file,$buff);
    }
    if ($chunkIndex + 1 === $totalChunks) {
        echo " Upload complete: {$fileName}";
    } else {
        echo "Chunk {$chunkIndex} / {$totalChunks} received.";
    }


    // if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    //     echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
    // } else {
    //     echo "Sorry, there was an error uploading your file.";
    // }
}

?>