<?php


/* Define our filter class */
class string_filter extends php_user_filter {
  var $mode;

  function filter($in, $out, &$consumed, $closing)
  {
    while ($bucket = stream_bucket_make_writeable($in)) {
        $d = $bucket->data.":".$bucket->datalen;
        echo $d;
        echo "----\n";
        if ($this->mode == 1) {
        $bucket->data = strtoupper($bucket->data);
        } elseif ($this->mode == 0) {
        $bucket->data = strtolower($bucket->data);
        }

        $consumed += $bucket->datalen;
        stream_bucket_append($out, $bucket);
    }
    return PSFS_PASS_ON;
  }

  function onCreate()
  {
    if ($this->filtername == 'str.toupper') {
      $this->mode = 1;
    } elseif ($this->filtername == 'str.tolower') {
      $this->mode = 0;
    } else {
      /* Some other str.* filter was asked for,
         report failure so that PHP will keep looking */
      return false;
    }

    return true;
  }
}

/* Register our filter with PHP */
stream_filter_register("str.*", "string_filter")
    or die("Failed to register filter");

$fp = fopen("foo-bar.txt", "w");

/* Attach the registered filter to the stream just opened
   We could alternately bind to str.tolower here */
stream_filter_append($fp, "str.toupper");

fwrite($fp, "Line1\n");
fwrite($fp, "Word - 2\n");
fwrite($fp, "Easy As 123\n");

fclose($fp);

/* Read the contents back out
 */
readfile("foo-bar.txt");


echo "---------------------------------------";
echo "\n";
$f = fopen(__DIR__."/big_file.txt","r");
class customFilter extends PHP_User_Filter {
    function onCreate () {
        return true;
    }
    public function filter($in,$out,&$consumer,$closing) {
        while($bucket = stream_bucket_make_writeable($in)) {
            $str_ = "You shut";
            $len = strlen($str_);
            $bucket->data = $str_;
            $consumer += $len;
            stream_bucket_append($out,$bucket);
            return PSFS_PASS_ON;
        }
        
    }
}
stream_filter_register("myfilter","customFilter");
stream_filter_append($f,"myfilter");
$f_ = stream_get_line($f,1024,"|");
echo $f_;
echo "\n--------------------------";
?>