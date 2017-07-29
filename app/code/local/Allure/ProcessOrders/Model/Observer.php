<?php
class Allure_ProcessOrders_Model_Observer{
    private $client;
	private $credentials;
	private $session;
	private $sessionId;
	
	private $currentOrder = null;
	private $orderInfo = null;
	private $itemsQty = null;
	
	function __construct($wsdl, $username, $apiKey) {
		ob_implicit_flush(true);
		ob_start();
		
		$this->client = new SoapClient($wsdl, array( 'connection_timeout' => 120));
		
		$this->credentials = array (
				'username' => $username,
				'apiKey' => $apiKey
		);
		
		try {
			$this->session = $this->client->login($this->credentials);
			$this->sessionId = $this->session->result;
		} catch (Exception $e) {
			die("Can not login. Reason: ".$e->getMessage());
		}
	}
	
	function __destruct() {
		$this->client->endSession(array('sessionId' => $this->sessionId));
	}
	
	private static function log($message) {
		
		var_dump($message);

//  		flush();
//  		ob_flush();
	}
	
	private function getOrderList($storeId, $status, $from) {
		$orderFilters = array (
			"complex_filter" => array (
				"complexObjectArray" => array (
					array (
						"key" => "store_id",
						"value" => array (
							"key" => "=",
							"value" => (string) $storeId 
						)
					),
					array (
						"key" => "status",
						"value" => array (
							"key" => "in",
								"value" => (string) $status 
						)
					),
					array (
						"key" => "created_at",
						"value" => array (
							"key" => "from",
							"value" => (string) $from 
						)
					)
				)
			)
		);
		try {
			$orderList = $this->client->salesOrderList(array('sessionId'=> $this->sessionId, 'filters'=> $orderFilters));
			
			//var_dump(array('sessionId'=> $this->sessionId, 'filters'=> $orderFilters));var_dump($orderList);die;
			
			if ($orderList->result && isset($orderList->result->complexObjectArray)) {
				return $orderList->result->complexObjectArray;
			}
			
			return array();
			
		} catch (Exception $e) {
			die('Could not fetch orders. Reason: '.$e->getMessage());
		}
	}
	
	private function getOrderInfo($orderIncrementId) {
		
		try {
			$orderInfo = $this->client->salesOrderInfo(array('sessionId' => $this->sessionId, 'orderIncrementId' => $orderIncrementId));
			
			return $orderInfo->result;
			
		} catch (Exception $e) {
			die('Could not fetch order info. Reason: '.$e->getMessage());
		}
	}
	
	private function getOrderItems($orderIncrementId) {
		
		try {
			$orderInfo = $this->getOrderInfo($orderIncrementId);
			
			return $orderInfo->items->complexObjectArray;
		} catch (Exception $e) {
			die('Could not fetch order items. Reason: '.$e->getMessage());
		}
	}
	
	private function getItemsQty($orderIncrementId) {
		
		$items = $this->getOrderItems($orderIncrementId);
		
		$itemsQty = array();
		
		if (is_object($items) && isset($items->item_id)) {
			$itemsQty[$items->item_id] = $items->qty_ordered;
		} else {
			
			foreach ($items as $item) {
				$itemsQty[$item->item_id] = $item->qty_ordered;
			}
		}
		
		return $itemsQty;
	}
	
	private function createInvoice($orderIncrementId, $itemsQty) {
		try {
			$invoiceInfo = $this->client->salesOrderInvoiceCreate(array('sessionId'=> $this->sessionId, 'invoiceIncrementId'=> $orderIncrementId, 'itemsQty' => $itemsQty));
			
			return $invoiceInfo->result;
		} catch (Exception $e) {
			//die('Could not create invoice for #'.$orderIncrementId.". Reason: ".$e->getMessage());
			var_dump('Could not create invoice for #'.$orderIncrementId.". Reason: ".$e->getMessage());
		}
	}
	
	private function createShipment($orderIncrementId, $itemsQty) {
		try {
			$shipmentInfo = $this->client->salesOrderShipmentCreate(array('sessionId'=> $this->sessionId, 'orderIncrementId'=> $orderIncrementId, 'itemsQty' => $itemsQty, 'email' => false, 'includeComment' => false));
			
			return $shipmentInfo->result;
		} catch (Exception $e) {
			//die('Could not create shipment for #'.$orderIncrementId.". Reason: ".$e->getMessage());
			var_dump('Could not create shipment for #'.$orderIncrementId.". Reason: ".$e->getMessage());
		}
	}
	
	private function addComment($orderIncrementId, $status, $comment) {
		try {
			$commentInfo = $this->client->salesOrderAddComment(array('sessionId'=> $this->sessionId, 'orderIncrementId'=> $orderIncrementId, 'status' => $status, 'comment' => $comment));
			
			return (bool) $commentInfo->result;
		} catch (Exception $e) {
			die('Could not add comment to #'.$orderIncrementId.". Reason: ".$e->getMessage());
		}
	}
	
	private function addInvoiceComment($orderIncrementId, $invoiceIncrementId) {
		$status = 'processing';
		$comment = "Created Invoice #$invoiceIncrementId";
		return $this->addComment($orderIncrementId, $status, $comment);
	}
	
	private function addShipmentComment($orderIncrementId, $shipmentIncrementId) {
		$status = 'complete';
		$comment = "Created Shipment #$shipmentIncrementId";
		return $this->addComment($orderIncrementId, $status, $comment);
	}
	
	private function processOrderStatus($order, $status) {
		$orderIncrementId = $order->increment_id;
		$invoiceIncrementId = null;
		$shipmentIncrementId = null;
		
		$this->currentOrder = $orderIncrementId;
		
		$this->log($orderIncrementId);
		
		switch ($status) {
			case 'pending':
				$itemsQty 				= $this->getItemsQty($orderIncrementId);
				$invoiceIncrementId		= $this->createInvoice($orderIncrementId, $itemsQty);
				$this->log($invoiceIncrementId);
				
				if ($invoiceIncrementId) {
					$this->addInvoiceComment($orderIncrementId, $invoiceIncrementId);
					
					$shipmentIncrementId	= $this->createShipment($orderIncrementId, $itemsQty);
					$this->log($shipmentIncrementId);
					
					if ($shipmentIncrementId)  {
						$this->addShipmentComment($orderIncrementId, $shipmentIncrementId);
					}
				}
				break;
			case 'processing':
				$itemsQty 				= $this->getItemsQty($orderIncrementId);
				$shipmentIncrementId	= $this->createShipment($orderIncrementId, $itemsQty);
				$this->log($shipmentIncrementId);
				if ($shipmentIncrementId)  {
					$this->addShipmentComment($orderIncrementId, $shipmentIncrementId);
				}
				break;
			case 'completing':
				$status = 'complete';
				$comment = "Completed Order #$orderIncrementId";
				$this->addComment($orderIncrementId, $status, $comment);
				break;
		}
		
		//var_dump($orderIncrementId);
		//var_dump($invoiceIncrementId);
		//var_dump($shipmentIncrementId);
	}
	
	public function processOrders() {
		$storeId 	= (isset($_GET['store']) && !empty($_GET['store'])) ? (int) $_GET['store'] : 2;
		$status  	= (isset($_GET['status']) && !empty($_GET['status'])) ? (string) $_GET['status'] : 'pending';
		$from 		= (isset($_GET['from']) && !empty($_GET['from'])) ? date("Y-m-d H:i:s", strtotime($_GET['from'],time())) : date("Y-m-d H:i:s", strtotime('first day of this month', time()));
		
		$ordersList = $this->getOrderList($storeId, $status, $from);
		
		if ($ordersList && count($ordersList)) {
			foreach ($ordersList as $order) {
				$this->processOrderStatus($order, $status);
			}
		}
		
		$this->log('DONE');
	}

    public function runProcess(){
        
    }
}
