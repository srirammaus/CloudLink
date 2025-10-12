
<?php
/**r	Open a file for read only. File pointer starts at the beginning of the file
w	Open a file for write only. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
a	Open a file for write only. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
x	Creates a new file for write only. Returns FALSE and an error if file already exists
r+	Open a file for read/write. File pointer starts at the beginning of the file
w+	Open a file for read/write. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
a+	Open a file for read/write. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
x+	Creates a new file for read/write. Returns FALSE and an error if file already exists */
/**
 * by default it fopen or streams having schema 
 */

// $f = file_get_contents(__DIR__."/big_file.txt");
// $f = fopen(__DIR__."/big_file.txt","r");
// // $f = fread($f,filesize(__DIR__."/big_file.txt")); //prints all
// while(1) {
//     $f_ = fgets($f); // here also you can define chunk
//     // $f_ = stream_get_line($f,1024,ftell($f));  //more custimizable best for binary , because u can use any delimiter , chunk limit in bytes
//     if(!$f_) {
//         // rewind($f);
//         break;
//     }
//     echo $f_;
    
//     // echo 

// }
// var_dump(stream_get_wrappers());
// fclose($f);

$f =fopen(__DIR__."/big_file.txt","r");

while (1) {
    $f_=fgets($f); //by default delimiter is \n
    if(!$f_) {
        break;
    }
    echo $f_;
}
fclose($f);
echo "-------------------------------------";

$f =fopen(__DIR__."/big_file.txt","r");
while(1) {
    $f_ = stream_get_line($f,1024,"|");
    if(!$f_) {
        break;
    } 
    echo $f_;
    echo "only one time";
}
fclose($f);

echo "---------------------------------------";

$f = fopen(__DIR__."/big_file.txt","r");
$f2 = fopen(__DIR__."/copy_file.txt","w");
$f3 = fopen(__DIR__."/copy_file3.txt","w");
while(1) {
    $f_ = fgets($f);

    if(!$f_) {
        break;
    }
    fputs($f2,$f_); //dest,src
}
rewind($f);
stream_copy_to_stream($f,$f3); // src,dest
fclose($f);
fclose($f2);

echo "----------------------------------";

print_r(stream_get_wrappers());

echo "-------------------------";


// Function to test whether php://temp stays in memory or moves to disk
function testTempStream($sizeInMB) {
    $bytes = $sizeInMB * 1024 * 1024;
    echo "Writing {$sizeInMB}MB of data...\n";

    // Open temp stream, set memory limit = 2MB
    $fp = fopen('php://temp/maxmemory:2097152', 'r+'); // 2MB limit

    // Write data
    fwrite($fp, str_repeat('A', $bytes)); // actaully the sceond parameter is times but we know the A is one byte so we are putting that much byte times to create A
    rewind($fp);

    // Print info
    $meta = stream_get_meta_data($fp);

    echo "Stream URI: " . ($meta['uri'] ?? 'N/A') . "\n";
    if (isset($meta['uri']) && strpos($meta['uri'], 'php://temp') === 0) {
        echo "✅ Still in memory (under 2MB limit)\n";
    } else {
        echo "⚠️ Exceeded limit — data spilled to a temporary file\n";
        echo "Temp file path: " . $meta['uri'] . "\n";
    }

    fclose($fp);
    echo "--------------------------------------------\n";
}

// Test with 1MB (should stay in memory)
testTempStream(1);

// Test with 5MB (should move to disk)
testTempStream(5);


echo "------------------------\n";
echo "Enter your name: ";
$f = fopen("php://stdin","r");

$name = fgets($f);
fclose($f);
$f = fopen("php://stdout","w");
fputs($f,$name." Mariappan");
fclose($f);

$stdin  = fopen("php://stdin", "r");
$stdout = fopen("php://stdout", "w");
$stderr = fopen("php://stderr", "w");

fwrite($stdout, "Enter first number: ");
$num1 = trim(fgets($stdin));

fwrite($stdout, "Enter second number: ");
$num2 = trim(fgets($stdin));

if (!is_numeric($num1) || !is_numeric($num2)) {
    fwrite($stderr, "Error: Both inputs must be numbers.\n");
    // exit(1);
}else {
    $result = $num1 + $num2;
    fwrite($stdout, "Result: $result\n");
}



fclose($stdin);
fclose($stdout);
fclose($stderr);
echo "----------------------------------";
fwrite(STDOUT, "Running process...\n"); //php script.php >output.log 2>error.log

fwrite(STDERR, "Warning: CPU usage high!\n"); //php script.php >output.log 2>error.log
echo "-------------------------------------";


$f = fopen(__DIR__."/big_file.txt","r");
stream_filter_append($f,"string.toupper");
$val = stream_get_line($f,1024,"\n");
echo $val. "hey";
fclose($f);
echo "\n------------------------";
$f_ = fopen(__DIR__."/copy_file.txt","r");
class customFilter extends PHP_User_Filter
{
  private $_data;

  // This is called when the filter is initialized.
  function onCreate()
  {
    $this->_data = '';
    echo "worked fine";
    return true;
  }

  // This is the main function that does the data conversion.
  public function filter($in, $out, &$consumed, $closing)
  {
    // Here, I'm reading all the stream data into the $_data variable.
    while($bucket = stream_bucket_make_writeable($in))
    {
      $this->_data .= $bucket->data;
      $this->bucket = $bucket;
      $consumed = 0;
    }

    // Now I process it and save it again to the bucket.
    //while using this closing if this mean this works at the end of the entire stream , untill it just put eveythingin data varibale
    if ($closing)
    {
      $consumed += strlen($this->_data);

      // Here's where I set the data to replace and do the replacement.
      $pattern = "/Kevin/m";
      $str = preg_replace($pattern,
                          'REDACTED',
                          $this->_data);

      $this->bucket->data = $str;
      $this->bucket->datalen = strlen($this->_data);

      if(!empty($this->bucket->data)) 
      {
        stream_bucket_append($out, $this->bucket);
      }

      // This PHP constant indicates that the filter returned a value in $out.
      return PSFS_PASS_ON;
    }

    // This PHP constant indicates that the filter didn't return a value.
    return PSFS_FEED_ME;
  }
}
stream_filter_register("myfilter",'customFilter');

stream_filter_append($f_,"myfilter");
$val2 = stream_get_line($f_,1024,"\n");
echo $val2;
echo "----------------";

?>