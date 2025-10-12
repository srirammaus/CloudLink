<?php
/**
 * This captcha only used in signup, for signin no human intervention captcha will be used 
 */
namespace utils;

include_once __DIR__."/../library/ExceptionHandler.php";
include_once __DIR__."/../library/log.lib.php";
class captcha {
    public function __construct () {

    }
    public static function getFile() {
        $file_names = [
            "captchacode.otf",
            "Moms_typewriter.ttf",
        ];
        $file_path = __DIR__."/../public/assets/fonts/captchaFonts/".$file_names[rand(0,count($file_names)-1)];
        if(!file_exists($file_path)) throw new \serverException(ErrorCode:"2200");
        $font_file = realpath($file_path);
        return $font_file;

    }
    /**
     * generate random string with numbers
     * Create Image
     * 
     */
    //old styl - imagestring($img, 1, 5, 5,  "A Simple Text String", $text_color);
    public static function generateCaptcha () {
        //generate code

        $char_len = 5;
        $chars = "0123456789qwertyuioplkjhgfdsazxcvbnm";
        $captcha_str = "";
        for($i=0;$i<= $char_len;$i++) {
            $captcha_str .= $chars[rand(0,strlen($chars) - 1)];
        }

        //@behind any function/expression supress errors and warning @imagecreate()
            
            //generate Image using image create function 
        $width = 100;
        $height = 50;
        $img = \imagecreate($width,$height);

        //fill the colors for created image 
        $background_color = imagecolorallocate($img, 0, 0, 0);
        $text_color = imagecolorallocate($img, rand(0,255),  rand(0,255),  rand(0,255));
        
        //setting the text
        $font_size = 10; //size
        $angle = 0; // degree
        $x = 20; //coordinates where the entire text starts i.e top(0,0)
        $y = 25; 
        $text_color = $text_color;
        $file = captcha::getFile();
        $text = $captcha_str;
        imagefttext($img,$font_size,$angle,$x,$y,$text_color,$file,$text);
        $_SESSION["captcha"] = $captcha_str;
        //returning the created image here , balance work carried by API
        return $img;
           
    }
    public function configureRedisSession(){

    }


}



?>