<?php
 require_once('../app/Mage.php'); 
umask(0);
Mage::app(); 

$fromYear = $_GET['from'];
$toYear = $_GET['to'];

if(empty($fromYear) && empty($toYear))
	die("Please Provide correct data!!!");

Mage::getModel('allure_counterpoint/data')->synkCounterpointOrders($fromYear,$toYear);

die("Finish!!!");

