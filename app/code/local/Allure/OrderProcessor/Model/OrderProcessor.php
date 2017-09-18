<?php

ini_set('xdebug.var_display_max_depth', 8);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Allure_OrderProcessor_Model_OrderProcessor
{
	private $helper;
	
	private $order;
	private $invoice;
	private $shipment;
	
	private $currentOrder = null;
	private $orderInfo = null;
	private $itemsQty = null;
	
	function __construct() {
		ob_implicit_flush(true);
		ob_start();
		
		$this->helper = Mage::helper('allure_orderprocessor');
		
		$this->order = Mage::getModel('sales/order_api');
		$this->invoice = Mage::getModel('sales/order_invoice_api');
		$this->shipment = Mage::getModel('sales/order_shipment_api');
	}
	
	private function log($message) {
		Mage::log($message, Zend_Log::DEBUG, 'order_processor.log', $this->helper->isDebugMode());
	}
	
	private function getOrderList($stores, $status, $from, $to) {
		$orderFilters = array (
			"store_id" => array (
				"in" => $stores
			),
			"status" => array (
				"in" => $status
			),
			"created_at" => array (
				"from" => (string) $from,
				"to" => (string) $to
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
		
		$this->log("Processing Order #".$orderIncrementId);
		
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
	}
	
	public function processOrders() {
		
		$helper = Mage::helper('allure_orderprocessor');
		
		if ($helper->isEnabled()) {
			
			$stores = explode(',', $helper->getStores());
			
			$status = explode(',', $helper->getStatusFilter());
			
			if (empty($status)) $status = array('pending');
			
			$from = $helper->getFromFilter();
			
			$to = $helper->getToFilter();
			
			$fromDate = new DateTime();
			
			if ($from && !empty($from)) {
				$fromDate->modify($from);
			} else {
				$fromDate->modify("-1 day");
			}
			
			$from = $fromDate->format('Y-m-d H:i:s');
			
			$toDate = new DateTime();
			
			if ($to && !empty($to)) {
				$toDate->modify($to);
			} else {
				$toDate->modify("-30 minutes");
			}
			
			$to = $toDate->format('Y-m-d H:i:s');
			
			if (!empty($stores)) {
			
				$this->log("STORES:: ".json_encode($stores));
				$this->log("STATUS:: ".json_encode($status));
				$this->log("FROM:: ".json_encode($from));
				$this->log("TO:: ".json_encode($to));
				
				$ordersList = $this->getOrderList($stores, $status, $from, $to);
				
				$this->log('Found '.count($ordersList).' orders...');
				
				if ($ordersList && count($ordersList)) {
					foreach ($ordersList as $order) {
						$this->processOrderStatus($order, $order['status']);
					}
				}
				
				$this->log('DONE');
			}
		}
	}
}