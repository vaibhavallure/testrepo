<?php

class Allure_CheckoutStep_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
   /* protected function _invoice()
    {
        $invoice = $this->getOrder()->prepareInvoice();

        $invoice->register();
        if ($this->getMethodInstance()->canCapture()) {
        	if(!$this->getOrder()->getIsReadyToShip()){ //mt-allure code add
            	$invoice->capture();
        	}
        }

        $this->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }*/
	
	protected $out_of_stock_order_id;
	
	public function setOutOfStockOrderId($id)
	{
		 $this->out_of_stock_order_id = $id;
	}
	
	public function getOutOfStockOrderId()
	{
		return $this->out_of_stock_order_id;
	}
	
	/**
	 * Authorize or authorize and capture payment on gateway, if applicable
	 * This method is supposed to be called only when order is placed
	 *
	 * @return Mage_Sales_Model_Order_Payment
	 */
	public function place()
	{
		$outOfStockOrderId = Mage::getSingleton('checkout/session')->getOutOfStockOrder();
		if(!isset($outOfStockOrderId) && empty($outOfStockOrderId))
			$outOfStockOrderId = 0;
			
		//Mage::log('in place : '.$outOfStockOrderId,Zend_log::DEBUG,'abc',true);
		Mage::dispatchEvent('sales_order_payment_place_start', array('payment' => $this));
		$order = $this->getOrder();
	
		$this->setAmountOrdered($order->getTotalDue());
		$this->setBaseAmountOrdered($order->getBaseTotalDue());
		$this->setShippingAmount($order->getShippingAmount());
		$this->setBaseShippingAmount($order->getBaseShippingAmount());
	
		$methodInstance = $this->getMethodInstance();
		$methodInstance->setStore($order->getStoreId());
		$orderState = Mage_Sales_Model_Order::STATE_NEW;
		$stateObject = new Varien_Object();
	
		/**
		 * Do order payment validation on payment method level
		 */
		$methodInstance->validate();
		$action = $methodInstance->getConfigPaymentAction();
		if ($action) {
			if ($methodInstance->isInitializeNeeded()) {
				/**
				 * For method initialization we have to use original config value for payment action
				 */
				$methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
			} else {
				$orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
				switch ($action) {
					case Mage_Payment_Model_Method_Abstract::ACTION_ORDER:
						$this->_order($order->getBaseTotalDue());
						break;
					case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
						$this->_authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
						$this->setAmountAuthorized($order->getTotalDue());
						break;
					case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
						if($order->getIsReadyToShip()){
							$this->_authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
							$this->setAmountAuthorized($order->getTotalDue());
						}else{
							$this->setAmountAuthorized($order->getTotalDue());
							$this->setBaseAmountAuthorized($order->getBaseTotalDue());
							
							//if($outOfStockOrderId==0)
								$this->capture(null);
							//else {
							//	$this->capture(null , $outOfStockOrderId);
								//Mage::log('order id 1 -'.$outOfStockOrderId,Zend_log::DEBUG,'abc',true);
							//}
						}
						break;
					default:
						break;
				}
			}
		}
	
		$this->_createBillingAgreement();
	
		$orderIsNotified = null;
		if ($stateObject->getState() && $stateObject->getStatus()) {
			$orderState      = $stateObject->getState();
			$orderStatus     = $stateObject->getStatus();
			$orderIsNotified = $stateObject->getIsNotified();
		} else {
			$orderStatus = $methodInstance->getConfigData('order_status');
			if (!$orderStatus) {
				$orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
			} else {
				// check if $orderStatus has assigned a state
				$states = $order->getConfig()->getStatusStates($orderStatus);
				if (count($states) == 0) {
					$orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
				}
			}
		}
		$isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
		$message = $order->getCustomerNote();
	
		// add message if order was put into review during authorization or capture
		if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
			if ($message) {
				$order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
			}
		} elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
			// add message to history if order state already declared
			$order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
		} elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
			// set order state
			$order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
		}
	
		Mage::dispatchEvent('sales_order_payment_place_end', array('payment' => $this));
	
		return $this;
	}
	
	
	/**
	 * Capture the payment online
	 * Requires an invoice. If there is no invoice specified, will automatically prepare an invoice for order
	 * Updates transactions hierarchy, if required
	 * Updates payment totals, updates order status and adds proper comments
	 *
	 * TODO: eliminate logic duplication with registerCaptureNotification()
	 *
	 * @return Mage_Sales_Model_Order_Payment
	 * @throws Mage_Core_Exception
	 */
	public function capture($invoice )
	{
		if (is_null($invoice)) {
			$invoice = $this->_invoice();
			$this->setCreatedInvoice($invoice);
			return $this; // @see Mage_Sales_Model_Order_Invoice::capture()
		}
		$amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
		
		//check backordered product is present or not.
		/* $outOfStockOrderId = Mage::getSingleton('checkout/session')->getOutOfStockOrder();
		if(!empty($outOfStockOrderId))
			$outOfStockOrder = Mage::getModel('sales/order')->load($outOfStockOrderId);
		else 
			$outOfStockOrderId = 0; */
		
		/* if($outOfStockOrderId!=0){
			$baseTotalForOutOfStockOrder = $outOfStockOrder->getBaseGrandTotal();
			$amountToCapture  = (float)$amountToCapture + (float)$baseTotalForOutOfStockOrder;
		} */
		
		$isSingleCharge = Mage::getSingleton('checkout/session')->getIsSingleCharge();
		if(!empty($isSingleCharge)){
			if($isSingleCharge){
				$backOrderBaseTotal = Mage::getSingleton('checkout/session')->getBaseTotal();
				if(!empty($backOrderBaseTotal)){
					if($backOrderBaseTotal!=0){
						$amountToCapture  = (float)$amountToCapture + (float)$backOrderBaseTotal;
					}
				}
			}
		}
		
		$order = $this->getOrder();
	
		// prepare parent transaction and its amount
		$paidWorkaround = 0;
		if (!$invoice->wasPayCalled()) {
			$paidWorkaround = (float)$amountToCapture;
			//check out of stcok products order id 
			/* if($outOfStockOrderId!=0){
				$baseTotalForOutOfStockOrder = $outOfStockOrder->getBaseGrandTotal();
				$paidWorkaround  = $paidWorkaround + $baseTotalForOutOfStockOrder;
			} */
		}
		$this->_isCaptureFinal($paidWorkaround);
	
		$this->_generateTransactionId(
				Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
				$this->getAuthorizationTransaction()
				);
	
		Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));
	
		/**
		 * Fetch an update about existing transaction. It can determine whether the transaction can be paid
		 * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
		 */
		if ($invoice->getTransactionId()) {
			$this->getMethodInstance()
			->setStore($order->getStoreId())
			->fetchTransactionInfo($this, $invoice->getTransactionId());
		}
		$status = true;
		if (!$invoice->getIsPaid() && !$this->getIsTransactionPending()) {
			// attempt to capture: this can trigger "is_transaction_pending"
			$this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);
	
			$transaction = $this->_addTransaction(
					Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
					$invoice,
					true
					);
	
			if ($this->getIsTransactionPending()) {
				$message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amountToCapture));
				$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
				if ($this->getIsFraudDetected()) {
					$status = Mage_Sales_Model_Order::STATUS_FRAUD;
				}
				$invoice->setIsPaid(false);
			} else { // normal online capture: invoice is marked as "paid"
				$message = Mage::helper('sales')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
				$state = Mage_Sales_Model_Order::STATE_PROCESSING;
				$invoice->setIsPaid(true);
				$this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
			}
			if ($order->isNominal()) {
				$message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
			} else {
				$message = $this->_prependMessage($message);
				$message = $this->_appendTransactionToMessage($transaction, $message);
			}
			$order->setState($state, $status, $message);
			$this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated
			
			$orderState = array('state'=>$state, 'status'=>$status,'message'=> $message);
			Mage::getSingleton('checkout/session')->setOrderStat($orderState);
			//here is back order products
			/* if($outOfStockOrderId!=0){
				$invoiceOut = Mage::getModel('sales/service_order', $outOfStockOrder)->prepareInvoice();
				$invoiceOut->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
				$invoiceOut->register();
				//$invoice->getOrder()->setCustomerNoteNotify(false);
				$invoiceOut->getOrder()->setIsInProcess(true);
				$invoiceOut->save();
				
				$outTotalPaid = $outOfStockOrder->getBaseGrandTotal();
				$outTotalDue = $outTotalPaid - $outOfStockOrder->getBaseGrandTotal();
				$outOfStockOrder->setTotalPaid($outTotalPaid);
				$outOfStockOrder->setTotalDue($outTotalDue);
				$outOfStockOrder->setState($state, $status, $message);
				//$this->getMethodInstance()->processInvoice($invoiceOut, $this); // should be deprecated
				$outOfStockOrder->save();
			}  */
			
			return $this;
		}
		
		Mage::throwException(
				Mage::helper('sales')->__('The transaction "%s" cannot be captured yet.', $invoice->getTransactionId())
				);
	}	
	
  }
