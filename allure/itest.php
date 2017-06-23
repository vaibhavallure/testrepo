<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);


require_once('../app/Mage.php');
umask(0);
Mage::app();

$client = new SoapClient(Mage::getBaseUrl('link', true).'api/v2_soap?wsdl=1', array( 'connection_timeout' => 120));

// If somestuff requires api authentification,
// then get a session token
$session = $client->login(array('username' => 'sureshinde','apiKey' => 'sunevenus'));

//var_dump($session);

//var_dump($client->__getFunctions());

$orderIncrementId = '2017003752-B';

if (isset($_GET['orderIncrementId'])) {
	$orderIncrementId = $_GET['orderIncrementId'];
}

$orderInfo = $client->salesOrderInfo(array('sessionId'=> $session->result, 'orderIncrementId'=>$orderIncrementId));

var_dump($orderInfo);

// If you don't need the session anymore
$client->endSession(array('sessionId' => $session->result));