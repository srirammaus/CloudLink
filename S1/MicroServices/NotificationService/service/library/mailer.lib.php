<?php
/**
 * {
*   "autoload": {
*       "files": [
**  "app/helpers.php"
*       ]
*   }
*  }
*        {
*  "autoload": {
*      "psr-4": {
**"App\\": "app/"
*      }
*  }
*   }
 */
namespace service\library;
include_once  __DIR__.'/../../library/general.lib.php';
include_once __DIR__."/../../library/ExceptionHandler.php";
include_once __DIR__."/../../library/log.lib.php";
require_once __DIR__."/../../vendor/autoload.php"; //always its better to use require_once or include_once becuase big projects ight get redclaration error


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class mailer {
    private $mail;
    public $reciever_email;

    public function __constuct () {

    }

    public function config () {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__,2)."/env",["mail_cred.env",".env"]);

        $dotenv->load();

        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();                                            
        $this->mail->Host       = $_ENV["MAIL_HOSTNAME"];                    
        $this->mail->SMTPAuth   = true;                             
        $this->mail->Username   = $_ENV["MAIL_USERNAME"];               
        $this->mail->Password   = $_ENV["MAIL_PASSWORD"];                 
        $this->mail->SMTPSecure = 'tls';                              
        $this->mail->Port       = $_ENV["MAIL_PORT"];  
  
        

    }
    /**
     * This can manipulated , that is called email spoofing , but by fdefault our smtp (gmail) server is checking that, so not possible 
     */
    public function setSender () {
        $this->mail->setFrom($_ENV["MAIL_SENDERMAIL"] , $_ENV["MAIL_NAME"] );           

    }
    /**
     * verifying the reciver mail id
     * setting cc,bcc, revicer 1, reciver 2 , reply to
     * compusorily try catch used before this function
     */
    public function setReciver () {
        $genLib = new \library\genLib;  
        echo $this->reciever_email;
        if($genLib->isValidUsername("temp",$this->reciever_email)) {
            $this->mail->addAddress($this->reciever_email , 'sriram mariappan');
            $this->mail->addAddress($this->reciever_email , 'sriram mariappan');

        }
        
        //$this->mail->addAddress('receiver1@gfg.com'); // optional
    }
    /**
     * This includes subject, body , AltBody ,attachments ,cc anthing comes this
     */
    public function setContent($content) {
        $this->mail->isHTML(true);                                  
        $this->mail->Subject = 'Subject';
        $this->mail->Body    = 'Your Email token is :  '. $content;;
        $this->mail->AltBody = 'Body in plain text for non-HTML mail ';

    }
    public function sendMail ($reciever_email,$content) {
        $this->reciever_email = $reciever_email;

        try {
            $this->config();
            $this->setSender();
            $this->setReciver();
            $this->setContent($content);
            $this->mail->send();
        }catch (\Throwable $e) {
            logg(file:"server_err",exception_: $e);
            //proceed further dont throw error 
            // throw new \serverException(ErrorCode:"1209");

        }
        
    }
}

?>