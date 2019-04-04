<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$type="day";

date_default_timezone_set('America/Los_Angeles');
$td_date=$current_date = date("Y-m-d");


$datetime = new DateTime('tomorrow');
$tw_date=$datetime->format('Y-m-d');



$allAppointments = Mage::getModel('appointments/appointments')->getCollection();
$allAppointments->addFieldToFilter('appointment_start', array('like' => $tw_date."%"));
$allAppointments->addFieldToFilter('last_notified', array('nlike' => $td_date."%"));
$allAppointments->addFieldToFilter('app_status',Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
$allAppointments->addFieldToFilter('store_id', array('eq' => 28));

echo "<br>Total Records Found:".$allAppointments->getSize()."<br>";

$allAppointments->getSelect()->limit(20);


if(empty($_GET['approve']))
{
    die("enter approve-----------------------");
}




Mage::getModel('appointments/cron')->sendNotification($allAppointments,"manual",$type);


die("done");

