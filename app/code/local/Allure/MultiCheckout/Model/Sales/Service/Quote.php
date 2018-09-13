<?php

/**
 * Quote submit service model
 */
class Allure_MultiCheckout_Model_Sales_Service_Quote extends Mage_Sales_Model_Service_Quote
{

    /*
     * In this method create orders without transaction as well as transactions.
     * if isPerformPayment attribute value is "FALSE" - order is without
     * Transaction
     * and
     * if if isPerformPayment attribute value is "TRUE" - order is with
     * Transaction
     *
     */
    public function submitCustomOrder ($isPerformPayment = false, $mainOrderId = 0, $isSingleCharge = false)
    {
        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();
        
        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $transaction->addObject($quote);
        
        if ($mainOrderId != 0) {
            $mainOrder = Mage::getModel('sales/order')->load($mainOrderId);
            $mainIncrementId = $mainOrder->getIncrementId();
            $newIncrementId = $mainIncrementId . "-B";
            $quote->setReservedOrderId($newIncrementId);
        } else {
            $quote->reserveOrderId();
        }
        
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()
                ->getCustomerAddress());
        }
        if (! $isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()
                    ->getCustomerAddress());
            }
        }
        $order->setPayment($this->_convertor->paymentToOrderPayment($quote->getPayment()));
        
        foreach ($this->_orderData as $key => $value) {
            $order->setData($key, $value);
        }
        
        foreach ($quote->getAllItems() as $item) {
            $orderItem = $this->_convertor->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()
                    ->getId()));
            }
            $order->addItem($orderItem);
        }
        
        $order->setQuote($quote);
        
        $transaction->addObject($order);
        if ($isPerformPayment) {
            $transaction->addCommitCallback(array(
                    $order,
                    'place'
            ));
        }
        
        // prepare backorder order state by main order.
        if ($mainOrderId != 0 && $isSingleCharge) {
            $orderState = Mage::getSingleton('checkout/session')->getOrderStat();
            $outTotalPaid = $order->getBaseGrandTotal();
            $outTotalDue = $outTotalPaid - $order->getBaseGrandTotal();
            // $order->setTotalPaid($outTotalPaid);
            $order->setTotalDue($outTotalDue);
            if (! empty($orderState)) {
                $order->setState($orderState['state'], $orderState['status'], $orderState['message']);
            } else {
                if ($mainOrderId != 0) {
                    if (! $mainOrder)
                        $mainOrder = Mage::getModel('sales/order')->load($mainOrderId);
                    $state = $mainOrder->getState();
                    $status = $mainOrder->getStatus();
                    $order->setState($state, $status);
                }
            }
            Mage::getSingleton('checkout/session')->setOrderStat(array());
        }
        
        $transaction->addCommitCallback(array(
                $order,
                'save'
        ));
        
        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array(
                'order' => $order,
                'quote' => $quote
        ));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array(
                'order' => $order,
                'quote' => $quote
        ));
        try {
            $transaction->save();
            $this->_inactivateQuote();
            
            // add existing transaction to backordered product
            if ($mainOrderId != 0 && $isSingleCharge) {
                $payment_method = $order->getPayment()
                    ->getMethodInstance()
                    ->getCode();
                
                // Keep order status as pending for banktransfer and
                // purchaseorder for singlecharge
                if ($payment_method != "banktransfer" && $payment_method != "purchaseorder" ) {
                   $transId = $this->createBackorderTransaction($mainOrderId, $order->getId());
                   $this->createBackorderInvoice($order->getId(), true);
                   $this->updateInvoice($order->getId(), $transId, $mainOrderId);
                }
            }            // check customer is wholesaller or not and payment is pay later
            else {
                $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
                $role = strtolower($role);
                if ('wholesale' == strtolower($role) && $order->getIsReadyToShip()) {
                    $payment_method = $order->getPayment()
                    ->getMethodInstance()
                    ->getCode();
                    if ($payment_method != "banktransfer" ){
                         $this->createBackorderInvoice($order->getId());
                    }
                }
            }
            
            Mage::dispatchEvent('sales_model_service_quote_submit_success', array(
                    'order' => $order,
                    'quote' => $quote
            ));
        } catch (Exception $e) {
            
            if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
                // reset customer ID's on exception, because customer not saved
                $quote->getCustomer()->setId(null);
            }
            
            // reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }
            
            Mage::dispatchEvent('sales_model_service_quote_submit_failure', array(
                    'order' => $order,
                    'quote' => $quote
            ));
            throw $e;
        }
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array(
                'order' => $order,
                'quote' => $quote
        ));
        $this->_order = $order;
        return $order;
    }

    /*
     * Create order without transaction.
     * In this orders contains only Out of stcok products.
     */
    public function submitCustomQuote ($mainOrderId, $isSingleCharge = false)
    {
        // don't allow submitNominalItems() to inactivate quote
        $shouldInactivateQuoteOld = $this->_shouldInactivateQuote;
        $this->_shouldInactivateQuote = false;
        try {
            $this->submitNominalItems();
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
        } catch (Exception $e) {
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
            throw $e;
        }
        // no need to submit the order if there are no normal items remained
        if (! $this->_quote->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }
        
        // first parameter : isPerformPayment transaction and second is order
        // id.
        if ($isSingleCharge)
            $this->submitCustomOrder(false, $mainOrderId, $isSingleCharge);
        else
            $this->submitCustomOrder(true, $mainOrderId, $isSingleCharge);
    }

    /*
     * Create orders with Transaction.
     *
     *
     */
    public function submitOrdersPayment ($mainOrderId = 0)
    {
        // don't allow submitNominalItems() to inactivate quote
        // Mage::log('in : '.$outOfStockOrderId,Zend_log::DEBUG,'abc',true);
        $shouldInactivateQuoteOld = $this->_shouldInactivateQuote;
        $this->_shouldInactivateQuote = false;
        try {
            $this->submitNominalItems();
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
        } catch (Exception $e) {
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
            throw $e;
        }
        // no need to submit the order if there are no normal items remained
        if (! $this->_quote->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }
        
        // first parameter : isPerformPayment transaction and second is order
        // id.
        $isSingleCharge = false;
        $this->submitCustomOrder(true, $mainOrderId, $isSingleCharge);
    }

    private function createBackorderInvoice ($orderId, $isPayNow = false)
    {
        $orderIn = Mage::getModel('sales/order')->load($orderId);
        $ordered_items = $orderIn->getAllItems();
        $savedQtys = array();
        foreach ($ordered_items as $item) { // item detail
            $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
        }
        $invoice = Mage::getModel('sales/service_order', $orderIn)->prepareInvoice($savedQtys);
        $captureCase = "not_capture";
        
        // if payment method is pay now
        if ($isPayNow) {
            $captureCase = "offline";
        }
        
        $invoice->setRequestedCaptureCase($captureCase);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        
        // if payment method is pay now
        if ($isPayNow) {
            $invoice->setState(2);
            $invoice->setCanVoidFlag(0);
        }
        
        // $invoice->save();
        $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject(
                $invoice->getOrder());
        $transactionSave->save();
    }

    private function updateInvoice ($orderId, $transId = 0, $mainOrderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        // if ($order->hasInvoices()) {
        if ($transId != 0) {
            foreach ($order->getInvoiceCollection() as $invoce) {
                $invoce->setState(2);
                $invoce->setCanVoidFlag(0);
                $invoce->setTransactionId($transId);
                $invoce->save();
            }
        }
        // }
        
        // set transactional details to the backorder
        $orderMain = Mage::getModel("sales/order")->load($mainOrderId);
        $paymentMain = $orderMain->getPayment();
        $payment = $order->getPayment();
        $payment->setAdditionalInformation($paymentMain->getAdditionalInformation())
            ->save();
    }

    private function createBackorderInvoice2 ($orderId)
    {
        $outOfStockOrder = Mage::getModel('sales/order')->load($orderId);
        $invoiceOut = Mage::getModel('sales/service_order', $outOfStockOrder)->prepareInvoice();
        $invoiceOut->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoiceOut->register();
        $invoiceOut->getOrder()->setIsInProcess(true);
        $invoiceOut->save();
    }

    private function createBackorderTransaction ($mainOrderId, $backOrderId)
    {
        $transactionInStock = Mage::getModel('sales/order_payment_transaction')->load($mainOrderId, 'order_id');
        $transId = 0;
        if ($transactionInStock->getId() != 0) {
            $transactionOut = Mage::getModel('sales/order_payment_transaction'); // ->load($outOfStockOrderId,'order_id');
            $transactionOut->setOrderId($backOrderId);
            $transactionOut->setOrderPaymentObject($transactionInStock->getOrderPaymentObject());
            $transactionOut->setTxnId($transactionInStock->getTxnId());
            $transactionOut->setTxnType($transactionInStock->getTxnType());
            $transactionOut->setIsClosed($transactionInStock->getIsClosed());
            
            $additinalInfo = $transactionInStock->getAdditionalInformation();
            if ($additinalInfo) {
                foreach ($additinalInfo as $key => $value) {
                    $transactionOut->setAdditionalInformation($key, $value);
                }
            }
            $transId = $transactionInStock->getTxnId();
            // $transactionOut->setAdditionalInformation($transactionInStock->getAdditionalInformation());
            $transactionOut->save();
        }
        return $transId;
    }
}
