<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);


require_once('../app/Mage.php');
umask(0);
Mage::app();

//$api = Mage::getSingleton('core/store_api_v2');

//$storeList = $api->items();

//var_dump($storeList);die;

$client = new SoapClient(Mage::getBaseUrl('link', true).'api/v2_soap?wsdl=1', array( 'connection_timeout' => 120));

// If somestuff requires api authentification,
// then get a session token
$credentials = array (
		'username' => 'sureshinde',
		'apiKey' => 'sunevenus' 
);
$session = $client->login($credentials);

//var_dump($session);

//var_dump($client->__getFunctions());

$storeList = $client->storeList(array('sessionId'=> $session->result));

var_dump($storeList);

$orderFilters = array (
		"complex_filter" => array (
				"complexObjectArray" => array (
						array (
								"key" => "store_id",
								"value" => array (
										"key" => "=",
										"value" => "1" 
								) 
						),
						array (
								"key" => "status",
								"value" => array (
										"key" => "in",
										"value" => "pending" 
								) 
						),
// 						array (
// 								"key" => "created_at",
// 								"value" => array (
// 										"key" => "from",
// 										"value" => "2017-06-19 4:00:00" 
// 								) 
// 						) 
				) 
		) 
);

// $orderFilters = array (
// 	"store_id"  => array ("eq" => "1"),
// 	"status" => array ("in" => "pending,processing,completing"),
// 	"created_at" => array ( "from" => "2017-06-17 4:00:00")
// );

//$api = Mage::getSingleton('sales/order_api_v2');
//$orderList= $api->items($orderFilters);

$orderList = $client->salesOrderList(array('sessionId'=> $session->result, 'filters'=> $orderFilters));

var_dump($orderList);die;

$orderIncrementId = '2017003752-B';

if (isset($_GET['orderIncrementId'])) {
	$orderIncrementId = $_GET['orderIncrementId'];
}

$orderInfo = $client->salesOrderInfo(array('sessionId'=> $session->result, 'orderIncrementId'=>$orderIncrementId));

var_dump($orderInfo);

// If you don't need the session anymore
$client->endSession(array('sessionId' => $session->result));