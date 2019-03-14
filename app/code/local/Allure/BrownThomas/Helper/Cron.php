<?php
class Allure_BrownThomas_Helper_Cron extends Mage_Core_Helper_Abstract
{
    private function config() {
        return Mage::helper("brownthomas/config");
    }
    private function data() {
        return Mage::helper("brownthomas/data");
    }
    private function sftp() {
        return Mage::helper("brownthomas/sftp");
    }
    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }


    public function getDiffUtc()
    {
         /* -- utc and backend set timezone -- */

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone($this->config()->getTimeZone());
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            return  $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            return  $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';

    }

    public function getCurrentDatetime()
    {
        $user_tz = new DateTimeZone($this->config()->getTimeZone());
        $user = new DateTime('now', $user_tz);
        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $ar=(array)$usersTime;
        $date = $ar['date'];
        return $date = strtotime($date);
    }

    public function getHour($datetime)
    {
       return date('H',  $datetime);
    }

    public function getMinute($datetime)
    {
        return date('i',  $datetime);
    }


}
