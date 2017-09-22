<pre>
<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

$orderIncrementId 	= (isset($_GET['itemId']) && !empty($_GET['itemId'])) ? (string) $_GET['itemId'] : '';

if (empty($orderIncrementId)) {
	die('OK');
}

try {

$client = new SoapClient('https://www.venusbymariatash.com/api/v2_soap?wsdl=1', array( 'connection_timeout' => 120));

// If somestuff requires api authentification,
// then get a session token
$credentials = array (
		'username' => 'sureshinde',
		'apiKey' => 'sunevenus' 
);
$session = $client->login($credentials);

//var_dump($session);

//var_dump($client->__getFunctions());

//$storeList = $client->storeList(array('sessionId'=> $session->result));

//var_dump($storeList);

$orderInfo = $client->salesOrderInfo(array('sessionId'=> $session->result, 'orderIncrementId'=> $orderIncrementId));
	
print_r($orderInfo);die;

//var_dump($orderList);

// If you don't need the session anymore
$client->endSession(array('sessionId' => $session->result));
} catch (Exception $e) {
	die($e->getMessage());
}