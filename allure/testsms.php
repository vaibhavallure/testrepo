<?php
require_once('app/Mage.php'); //Path to Magento
ini_set('display_errors', 1);
umask(0);
Mage::app();

/* try {
$api = new SoapClient('https://www.smsglobal.com/mobileworks/soapserver.php?wsdl',array( 'cache_wsdl' => WSDL_CACHE_NONE,'soap_version'   => SOAP_1_1));
$session = $api->apiValidateLogin('s51fn3hf','pEtXTfsk');
var_dump($session);die;

preg_match("/<ticket>(?<ticket>.+)<\/ticket>/", $session, $response);
//var_dump($response);
//die(json_encode($response));
//$status = $api->apiSendSms($response['ticket'], 'VMT', '+919423842431', 'Welcome', 'text', '0', '0');
$status = $api->__soapCall('apiSendSms',$response['ticket'], $helper::SMS_FROM, '+919762361838', $text, 'text', '0', '0');
//var_dump($status);

preg_match("/<resp err=\"(?<error>.+)\">(<res>(<dest>(?<dest>.+)<\/dest>)?(<msgid>(?<msgid>.+)<\/msgid>)?.*<\/res>)?<\/resp>/", $status, $statusData);
var_dump($statusData);die($status);
} catch (Exception $e) {
	var_dump($e->getTrace());
	die($e->getMessage());
} */




