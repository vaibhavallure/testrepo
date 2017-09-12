<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

//$api = Mage::getSingleton('core/store_api_v2');

//$storeList = $api->items();

//var_dump($storeList);die;

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

$orderFilters = array (
		"complex_filter" => array (
				"complexObjectArray" => array (
						array (
								"key" => "store_id",
								"value" => array (
										"key" => "=",
										"value" => "2" 
								) 
						),
						array (
								"key" => "status",
								"value" => array (
										"key" => "in",
										"value" => "pending" 
								) 
						),
						array (
								"key" => "created_at",
								"value" => array (
										"key" => "from",
										"value" => "2017-06-19 4:00:00" 
								) 
						) 
				) 
		) 
);

$orderList = $client->salesOrderList(array('sessionId'=> $session->result, 'filters'=> $orderFilters));

foreach ($orderList->result->complexObjectArray as $order) {
	$orderIncrementId = $order->increment_id;
	
	$orderInfo = $client->salesOrderInfo(array('sessionId'=> $session->result, 'orderIncrementId'=> $orderIncrementId));
	
	
	$items = $orderInfo->result->items->complexObjectArray;
	
	$itemsQty = array();
	
	if (is_object($items) && isset($items->item_id)) {
		$itemsQty[$item->item_id] = $item->qty_ordered;
	} else {
	
		foreach ($items as $item) {
			$itemsQty[$item->item_id] = $item->qty_ordered;
		}
	}
	
	$invoiceInfo = $client->salesOrderInvoiceCreate(array('sessionId'=> $session->result, 'invoiceIncrementId'=> $orderIncrementId, 'itemsQty' => $itemsQty));
	
	
	$invoiceId = $invoiceInfo->result;
	
	if ($invoiceId) {
		$orderComment = "Created Invoice #$invoiceId";
		
		$commentInfo = $client->salesOrderAddComment(array('sessionId'=> $session->result, 'orderIncrementId'=> $orderIncrementId, 'status' => 'processing', 'comment' => $orderComment));
	
	
		$shipmentInfo = $client->salesOrderShipmentCreate(array('sessionId'=> $session->result, 'orderIncrementId'=> $orderIncrementId, 'itemsQty' => $itemsQty, 'email' => false, 'includeComment' => false));
		
		
		$shipmentId = $shipmentInfo->result;
	
		if ($shipmentId) {
			$orderComment = "Created Shipment #$shipmentId";
			
			$commentInfo = $client->salesOrderAddComment(array('sessionId'=> $session->result, 'orderIncrementId'=> $orderIncrementId, 'status' => 'complete', 'comment' => $orderComment));
		}
	}
	
	//die($orderIncrementId);
	var_dump($orderIncrementId);
	var_dump($invoiceId);
	var_dump($shipmentId);
	var_dump($commentInfo->result);
	//die;
}

//var_dump($orderList);

// If you don't need the session anymore
$client->endSession(array('sessionId' => $session->result));
} catch (Exception $e) {
	die($e->getMessage());
}