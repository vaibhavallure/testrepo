<?php
require_once '../../app/Mage.php';
umask(0);
Mage::app();

if(!isset($_GET['storeid']))
{
    die();
}



    $config=Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    $store=$_GET['storeid'];
    $storeKey = array_search ($store, $config['stores']);
    $timezone = $config['timezones'][$storeKey];
    date_default_timezone_set($timezone);
    $storeDate=date('Y-m-d H:i:s');

     $nextTime =$storeDate;
     $next2Time= date("Y-m-d 23:59:59",strtotime($storeDate));


    $allAppointments = Mage::getModel('appointments/appointments')->getCollection();
    $allAppointments->addFieldToFilter('appointment_start', array('gteq' => $nextTime));
    $allAppointments->addFieldToFilter('appointment_start', array('lteq' => $next2Time));
    $allAppointments->addFieldToFilter('app_status',Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
    $allAppointments->addFieldToFilter('store_id', array('eq' => $store));


if(count($allAppointments)>0)
{
Mage::getModel('appointments/cron')->sendNotification($allAppointments);
}


















date_default_timezone_set('UTC');
