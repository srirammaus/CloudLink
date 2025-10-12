<?php 

/**
 * This class should be autoloaded
 * A user may signup or loging from any part of world ,SO UTC time mandatoryr
 * 
 * 
 * On each release of PHP, the most current timezonedb package data is incorporated into the PHP distribution.
 * However, a new release of the time zone database doesn't necessarily force a release of PHP. 
 * Therefore, if you want the most current version of the time zone data, then your only option is to update the timezonedb package yourself.
 * 
 * On Linux, it comes from your OS tzdata package (apt update tzdata on Ubuntu/Debian).
 * On Windows, PHP bundles its own tzdata snapshot, updated only when you update PHP itself (unless you install PECL timezonedb).
 *
 * DateTimeZone itself is not the database.
 * It is a PHP interface to the IANA Time Zone Database (tzdata), which PHP uses internally.
 * 
 * 
 * 
 * 
 * Installation of timezonedb(tzdata):
 * 🔹 Why you don’t see php_timezonedb.dll
 
    * On Windows, PHP already ships with a bundled copy of the IANA timezone database inside the core php.exe + php_date.dll extension.

    * That’s why you don’t see a separate timezonedb.dll in your XAMPP installation.

    * The timezonedb extension is optional and only needed if:

    * You want to update tzdata without upgrading PHP itself.

    * Example: a country suddenly changes its DST rules, and your PHP’s bundled tzdata is outdated.

    * So, out of the box, your PHP (inside XAMPP) uses the built-in tzdata snapshot that came with your PHP version (likely from 2021 in your case).

    * 🔹 If you need newer tzdata (without upgrading XAMPP/PHP)

    * Download the timezonedb DLL for your PHP version + architecture (from PECL timezonedb
    * ).
    * https://windows.php.net/downloads/pecl/releases/timezonedb
    * Place it into your ext/ folder.

    * Enable it in php.ini:

    * extension=php_timezonedb.dll


    * Restart Apache.

    * This overrides the built-in snapshot with the newer tzdata.
 */
namespace utils;
//include Event manager
date_default_timezone_set("UTC");

class timeManager {
    public function __construct () {
        $this->warnTzOutdated(); //send email to administator
    }
    public static function utcNow() {
        return date("Y-m-d H:i:s");
    }
    public static function utcNowSeconds() {
        return time();
    }
    public static function secondsTotimestamp($seconds) {
        return date("Y-m-d H:i:s",$seconds);
    }
    public static function utcTolocal (string $utcString ,string $region="Asia/Kolkata") { // india
        $cdate = new \DateTime($utcString);
        $cdate->setTimezone(new \DateTimeZone($region));
        return $cdate->format("Y-m-d H:i:s");
    }
    public static function localToutc (string $localTime,string $region="Asia/Kolkata") {
        $cdate = new \DateTime($localTime, new \DateTimeZone($region));
        $cdate->setTimezone(new \DateTimeZone("UTC"));
        return $cdate->format("Y-m-d H:i:s");  

    }
    public function warnTzOutdated () {
        return timezone_version_get();
    }
}
?>