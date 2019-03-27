<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$type="nd";

if(empty($_GET['app']))
{
    die("enter app id");
}

if(isset($_GET['type']))
{
    $type=$_GET['type'];
}


$app=explode(",",$_GET['app']);

$allAppointments=array();

foreach ($app as $ap)
{
    $allAppointments[]= Mage::getModel('appointments/appointments')->load($ap);
}




Mage::getModel('appointments/cron')->sendNotification($allAppointments,"manual",$type);

//Mage::getModel('appointments/cron')->autoProcess();

die();

