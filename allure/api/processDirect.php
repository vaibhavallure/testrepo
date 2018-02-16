<?php

ini_set('xdebug.var_display_max_depth', 8);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

require_once('../../app/Mage.php');
umask(0);
Mage::app();

class OrderProcessor{
	private $order;
	private $invoice;
	private $shipment;
	
	private $currentOrder = null;
	private $orderInfo = null;
	private $itemsQty = null;
	
	function __construct() {
		ob_implicit_flush(true);
		ob_start();
		
		$this->order = Mage::getModel('sales/order_api');
		$this->invoice = Mage::getModel('sales/order_invoice_api');
		$this->shipment = Mage::getModel('sales/order_shipment_api');
	}
	
	private static function log($message) {
		
		var_dump($message);

//  		flush();
//  		ob_flush();
	}
	
	private function getOrderList($storeId, $status, $from) {
		$orderFilters = array (
			"store_id" => array (
				"=" => (string) $storeId
			),
			"status" => array (
				"in" => (string) $status 
			),
			"created_at" => array (
				 "from" => (string) $from 
			)
		);
		try {
			$orderList = $this->order->items($orderFilters);
			
			return $orderList;
			
		} catch (Exception $e) {
			die('Could not fetch orders. Reason: '.$e->getMessage());
		}
	}
	
	private function getOrderInfo($orderIncrementId) {
		
		try {
			$orderInfo = $this->order->info($orderIncrementId);
			
			return $orderInfo;
			
		} catch (Exception $e) {
			die('Could not fetch order info. Reason: '.$e->getMessage());
		}
	}
	
	private function getOrderItems($orderIncrementId) {
		
		try {
			$orderInfo = (object) $this->getOrderInfo($orderIncrementId);
			
			return $orderInfo->items;
		} catch (Exception $e) {
			die('Could not fetch order items. Reason: '.$e->getMessage());
		}
	}
	
	private function getItemsQty($orderIncrementId) {
		
		$items = $this->getOrderItems($orderIncrementId);
		
		$itemsQty = array();
		
		if (is_object($items) && isset($items['item_id'])) {
			$items = (object) $items;
			$itemsQty[$items->item_id] = $items->qty_ordered;
		} else {
			
			foreach ($items as $item) {
				$item = (object) $item;
				$itemsQty[$item->item_id] = $item->qty_ordered;
			}
		}
		
		return $itemsQty;
	}
	
	private function createInvoice($orderIncrementId, $itemsQty) {
		try {
			$invoiceInfo = $this->invoice->create($orderIncrementId, $itemsQty);
			
			return $invoiceInfo;
		} catch (Exception $e) {
			//die('Could not create invoice for #'.$orderIncrementId.". Reason: ".$e->getMessage());
			$this->log('Could not create invoice for #'.$orderIncrementId.". Reason: ".$e->getMessage());
		}
	}
	
	private function createShipment($orderIncrementId, $itemsQty) {
		try {
			$shipmentInfo = $this->shipment->create($orderIncrementId, $itemsQty, false, false);
			
			return $shipmentInfo;
		} catch (Exception $e) {
			//die('Could not create shipment for #'.$orderIncrementId.". Reason: ".$e->getMessage());
			$this->log('Could not create shipment for #'.$orderIncrementId.". Reason: ".$e->getMessage());
		}
	}
	
	private function addComment($orderIncrementId, $status, $comment) {
		try {
			$commentInfo = $this->order->addComment($orderIncrementId, $status, $comment);
			
			return (bool) $commentInfo;
		} catch (Exception $e) {
			$this->log('Could not add comment to #'.$orderIncrementId.". Reason: ".$e->getMessage());
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
		
		$order = (object) $order;
		
		$orderIncrementId = $order->increment_id;
		$invoiceIncrementId = null;
		$shipmentIncrementId = null;
		
		$this->currentOrder = $orderIncrementId;
		
		$this->log($orderIncrementId);
		
		//var_dump($this->getItemsQty('L2017007293'));die;
		
		//$this->log($status);
		
		try {
		
			switch ($status) {
				case 'pending':
					$itemsQty 				= $this->getItemsQty($orderIncrementId);
					$invoiceIncrementId		= $this->createInvoice($orderIncrementId, $itemsQty);
					
					if ($invoiceIncrementId) {
						$this->log('Created Invoice #'.$invoiceIncrementId);
						$this->addInvoiceComment($orderIncrementId, $invoiceIncrementId);
						
						$shipmentIncrementId	= $this->createShipment($orderIncrementId, $itemsQty);
						
						if ($shipmentIncrementId)  {
							$this->log('Created Shipment #'.$shipmentIncrementId);
							$this->addShipmentComment($orderIncrementId, $shipmentIncrementId);
						}
					}
					break;
				case 'processing':
					$itemsQty			= $this->getItemsQty($orderIncrementId);
					$shipmentIncrementId	= $this->createShipment($orderIncrementId, $itemsQty);
					if ($shipmentIncrementId)  {
						$this->log('Created Shipment #'.$shipmentIncrementId);
						$this->addShipmentComment($orderIncrementId, $shipmentIncrementId);
					}
					break;
				case 'completing':
					$status = 'complete';
					$comment = "Completed Order #$orderIncrementId";
					$this->addComment($orderIncrementId, $status, $comment);
					break;
			}
		} catch (Exception $e) {
			$this->log($e->getMessage());
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
		
		$this->log('Found '.count($ordersList).' orders...');
		
		if ($ordersList && count($ordersList)) {
			foreach ($ordersList as $order) {
				$this->processOrderStatus($order, $status);
			}
		}
		
		$this->log('DONE');
	}
}

$orderProcessor = new OrderProcessor();

$orderProcessor->processOrders();