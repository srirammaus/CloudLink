<?php

/***
 * This is a library used by notifyCloudLinkApp and other exec file 
 * becuase they have to update what happen or activities that made to user
 * 
 */
namespace library;


class AppNotificationManager {
    public function __construct () {

    }
    /**
     * get specific user by username and check whether their row with any "seen is 0", they are non viewedd by user data
     * so it has to get by this function .
     */
    public function getNewData () {

    }
    /**
     * default index = 0 
     * get All data of specific use, user limit to fetch first 10 
     * and in the return statement give last index as 10 ,when user clicks show more use the last index as index and 10 to 20 then last index is 20..so on
     * 
     */
    public function getAllData () {

    }
    /**
     * why we didn't use getNewData , because thats overloading or gicing more load to the user
     */
    public function countUnread() {

    }
    /**
     * setting new Data data
     * @param notification id unique
     * @param username
     * @param iconURL  -CDN
     * @param description 
     * @param is_read
     * @param timestamp - UTC timestamp not in seconds
     * @param actionURL - by default null , you can also place anyURL to redirect
     * @param metadata - optional
     */
    public function setNewData () {

    }
    /**
     * update the seen zone or read of user by indicating 1 or 0, for specfic notification id
     */
    public function updateData () {

    }
    /**
     * marking or updating all the notification to read 1
     */
    public function markAllasread () { 

    }
    /**
     * archive somerecords in db , handled by event scheduler 1 day once
     * moving to some non usable places
     */
    public function archiveRecord () {

    }
}
?>