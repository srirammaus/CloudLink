<?php 
/**
 * Anything in else , should throw new Exception...
 */

namespace library;
include_once "./config/db_conn.php";
include_once "ExceptionHandler.php";
include_once "log.lib.php";
include_once "general.lib.php";
include_once __DIR__."/../Events/EventProducer.svc.php";

use function \library\logg;
use \utils\timeManager;  // I'm getting this without including , do you know how,actually i require it in signup.api (via autoload) so i can use it here

class signup  {
        public $username ;
        public $password ;
        public $phone ;
        public $email ;
        public $secondary_email ;
        public $bio ;
        public $avatar ; //
        public $name;

        public $userid;
        public $hashed_pwd;
        public $email_token;
        public $phone_otp;
        public $phone_otp_expiry;
        public $email_token_expiry;
        public $verified_E;
        public $verified_M;
    public function __construct () {

    }

    public function getConn () {
        $conn = new \db\db_conn;
        return $conn->conn();
    }
    
    /**
     * generate token and set expiry for this
     * 3 min - 
     * Sending the Email to Event broker (Service name: Notification Service)
     * email in partion 0
     */
    public function emailToken() {
        $byteLen = 16;
        $this->email_token =  \library\genLib::generateToken($byteLen);
        $this->email_token_expiry = timeManager::utcNowSeconds() + 180; //180-3min

        

    }
    /**
     * generate OTP and set expiry for this
     * OTP in partion 1
     */
    public function phoneOTP () {
        $this->phone_otp =  \library\genLib::generateOTP();
        $this->phone_otp_expiry = timeManager::utcNowSeconds() + 180; //180-3min

    }
    /**
     * This uses the serivce/EventManager.php to send the messages to notification service
     * call event producer lib here
     */
    public function sendToNotificationService() {
        try{

            $ep = new \Events\EventProducer;
            $ep->createProducer();
            $ep->setTopic("user-events");
            if(!empty($this->email) && !empty($this->email_token) ) {
                $value = [
                    "email" => $this->email,
                    "email_token" => $this->email_token,
                ];
                $value = json_encode($value);
                $ep->setEvent(0,0,$value,"send-email-verification");
                $ep->emitEvent();
            }

        }catch (\RdKafka\KafkaErrorException $e) {
            logg(file:"kafka_err",message: $e);
            //proceed further no need to throw other exception
        }catch(\Throwable $e) {
            logg(file:"kafka_err",message: $e);

        }

    }
    public function HashPwd () {
        $SALT = "lkjfsdfksdnfnsdkfnsdkfslkdnfklsdfldsnfndslfnsdklndf";
        $combinedPwd = $this->password.$SALT;
        $this->hashed_pwd = password_hash($combinedPwd,PASSWORD_BCRYPT); //bcrypt algorithm
    }
    /**
     * check whether Bio string has any unsafe words
     */
    public function isValidBio () {
        if(strlen($this->bio) < 120) {
            $genLib = new \library\genLib();
            return $genLib->vulgerWordChecker($this->bio) == 0? true: throw new \clientException(ErrorCode:"1401");
        }else {
            throw new \clientException(ErrorCode:"1400");
        }
    
        
    }
    /**
     * It defines whethere the account is active or inactive or deleted or blocked (reported for any activity)
     * active - 1
     * inactive - 0
     * deactived- -1
     * deleted - -2
     * blocked or reported - -3
     * Mostly phone number verification is enough to bring the active status 1
     */
    public function getstatus(){
        return 0;
    }
    public function storeAvatar () {

    }

    /**
     * before generating userid know one thing that UUID has version from 1 to 8
     * each version has uniquenesss
     *  here lets use v4 - just random hexadecimal 128 bits
     *  v4- 32 char hexadecimal string -  which is 16 bytes
     *  v4  - 4 hyphens [hyphens not included in 16 bytes , but included to make 36 chars]
     * 
     * v4 rules - data[6] is 0100 is 4 [to labeling the ID , this is v4]
     * v4 rules - data[8] make the 6,7 bits of bytes value to "10""other balance 6 bits 0 or 1 remains here", it might give hexdecimal value of 8 or 9 or a, b
     */
    public function genUserID () {
        $data = random_bytes(16);
        //chr - rewriting  ascii values(decimal,0xff) to real char like M or m
        //ord - changing anything ("0xff",E)to ascii(decimal)

        // Set version to 0100 // for example if we get answer of this ((ord($data[6]) & 0x0f | 0x40)) = 77(decimal) , chr change to M anyhow finally in hexdecimal it is 4d, so we mentiond 4th version UUID
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10 // forexamplce data[8] is 1111 1111 then has to change 1011 1111 , now 6,7 bits are changed , it hex might contains 8 or 9 or a or b
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        $result = str_split(bin2hex($data),4);
        $this->userid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        
    }

    /** 
     * @param username
     * @param password
     * @param phone
     * @param userid
     * @param email
     * @param secondary_email
     * @param bio
     * @param avater - True img forwared to CDN and image URL should stored in DB
     * validate whether the user is existing or not 
     * generate userid 
     * check the username is not unpleasant word
     * hash the password
     * check the bio limit has been exceeded
     * avatar image should be stored in CDN and respected URL has to fetched and stored in our db
     * whereever the NULL is present they are all optional , but all keys should present
     */
    public function createUser (array $requestParams) {

        $genLib = new \library\genLib;
        $vaildparams = ["username","password","email","name","secondary_email","phone","bio","captcha"]; 
        logg(file:"backend_log", message: json_encode($requestParams));
        // echo $_SESSION["captcha"];
        if ($genLib->isValidParams($vaildparams,$requestParams)) {
            if($_SESSION["captcha"] == $requestParams["captcha"]) {  //this == i changed it temoprily !=

                $this->username = $requestParams["username"];
                $this->name = $requestParams["name"];
                $this->password = $requestParams["password"];
                $this->phone = $requestParams["phone"];
                $this->email = $requestParams["email"];
                $this->secondary_email = $requestParams["secondary_email"] ?? NULL;
                $this->bio = $requestParams["bio"] ?? NULL;
                $this->avatar = $requestParams["avatar"]?? NULL; //true img forwared to CDN and image URL should stored in DB
                

                $this->verified_E = 0;
                $this->verified_M = 0;
                $currentTime = timeManager::utcNow();
                // echo $currentTime."\n";
                // $cTime = timeManager::utcTolocal($currentTime);
                if($genLib->isValidUsername($this->username,$this->email) && $genLib->isValidPhone($this->phone) ) {
                    if(!$genLib->isExistingUser($this->username,$this->email)) { //its giving only one return false so i place throw error here
                        if(!$genLib->isExistingPhone($this->phone)) {
                            $this->genUserID();
                            $this->HashPwd();
                            $this->isValidBio();
                            $this->emailToken();
                            $this->phoneOTP();

                            try {
                                $query = "INSERT INTO users (userid,username,name,password,pending_email,
                                secondary_email,phone,bio,avatar,profile_created,status,
                                email_token,phone_otp,verified_E,verified_M,
                                email_token_expiry,phone_otp_expiry
                                ) 

                                VALUES (:userid,:username,:name,:password,:pending_email,:secondary_email,
                                :phone,:bio,:avatar,:profile_created,:status,
                                :email_token,:phone_otp,:verified_E,:verified_M,
                                :email_token_expiry,:phone_otp_expiry)";

                                $prepared_statement = $this->getConn()->prepare($query);
                                $prepared_statement->bindParam(":userid",$this->userid);
                                $prepared_statement->bindParam(":username",$this->username);
                                $prepared_statement->bindParam(":name",$this->name);
                                $prepared_statement->bindParam(":password",$this->hashed_pwd);
                                $prepared_statement->bindParam(":pending_email",$this->email);
                                $prepared_statement->bindParam(":secondary_email",$this->secondary_email);
                                $prepared_statement->bindParam(":phone",$this->phone);
                                $prepared_statement->bindParam(":bio",$this->bio);
                                $prepared_statement->bindParam(":avatar",$this->avatar);
                                $prepared_statement->bindParam(":profile_created", $currentTime);
                                $prepared_statement->bindParam(":status",$this->getStatus());
                                $prepared_statement->bindParam(":email_token",$this->email_token);
                                $prepared_statement->bindParam(":phone_otp",$this->phone_otp);
                                $prepared_statement->bindParam(":email_token_expiry",$this->email_token_expiry);
                                $prepared_statement->bindParam(":phone_otp_expiry",$this->phone_otp_expiry);

                                $prepared_statement->bindParam(":verified_E",$this->verified_E);
                                $prepared_statement->bindParam(":verified_M",$this->verified_M);


                                $success = $prepared_statement->execute();
                                if($success) {
                                    // $this->sendToNotificationService();
                                    return [
                                        "flag" => "1",
                                        "message" => "sucessfully inserted, please check your mail and activate your account ",
                                    ];
                                }
                            }
                            /**
                             * you may have confusion  1. why im  catching here and 2.why im logging eventhough we having throw statement
                             * 1. here by default the pdo providing the own exception class (PDOException) , so im catching here.
                             *  if not catching here also not problem  but it considerd uncaught exception or caught by throwable
                             * so response sent as "Internal server error" and actual real error(i.e no column named useriiid) logged
                             * but we know that is a database error , i want client should aware that this is a db error
                             * so i catching here and using try .. catch (PDOExcep) . assume if u dont pdoexception , you should use try..catch(throwbale)
                             * another main point is i comment out some stuffs like if($result) {...}else {..}  this is also
                             * type of case ,some modules provide errors in seperate methods (here $prepared_statement->errorInfo()), what 
                             * developers do is that , we make if () else(false) {now erroInfo } - in this errorinfo CAUGHT the error. now
                             * the matter is here also catching happens.
                             * 2.why log: because here catch happens now i logs the real error and then send "Database err" to client ,
                             * same should be apllied for if else case . so dont confused why "loggin" in  SOME "else" places and "thwoning" in SAAME "else" places 
                             * so here is catching, happens so we have to log here 
                             * 
                             */
                            catch(\PDOException $e) { //This is more subclassification as of now it is okay
                                logg(file:"db_err",exception_: $e);
                                throw new \databaseException(ErrorCode:"1803");
                            }
                            
                            // $result = $prepared_statement->execute();
                            // if($result) {
                            //     $rowAff = $prepared_statement->rowCount();
                            //     $flag =1;
                            // }else {
                            //     $err = $prepared_statement->errorInfo();
                            //     throw new \databaseException(ErrorCode:"1803");
                            // }
                        }else {
                            throw new \clientException(ErrorCode:"1304");
                        }

                    }else {
                        throw new \clientException(ErrorCode:"1004");
                    }
                }else {
                    /**
                     * Why im putting server exception here , even though errors are THROWING is isvalidusername, because the 
                     * isvalidusername has throw new (..) , if anyhow something unexecpeted happens there need to be else condition thats why i put server error here
                     */
                    throw new \serverException(ErrorCode:"2003");
                }
            }else {
                throw new \clientException(ErrorCode:"1905");
            }
        }else {
            throw new \clientException(ErrorCode:"1904");
        }

        
    }
    public function TempDataUpload () {
        $username = "admin";
        $password = "admin";
        $email = "admin@admin.com";

        $sql = "INSERT INTO users (username, password, email)
                    VALUES ('John', 'Doe', 'john@example.com')";

        $this->getConn()->exec($sql);
        return array("message"=>"successfull updated..",);

    }
}

?>