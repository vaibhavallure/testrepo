<?php

class Allure_Signifyd_Model_Rewrite_Observer extends Signifyd_Connect_Model_Observer
{
    public function openCase($observer, $updateOnly = false)
    {
        try {
            $orders = array();
            
            if ($observer->getEvent()->hasOrder()) {
                // Onepage checkout and API
                $orders[] = $observer->getEvent()->getOrder();
            } elseif ($observer->getEvent()->hasOrders()) {
                // Multishipping
                $orders = $observer->getEvent()->getOrders();
            } else {
                // Look for registry key, for methods that open case on other events than sales_order_place_after
                $incrementId = Mage::registry('signifyd_last_increment_id');
                Mage::unregister('signifyd_last_increment_id');
                
                if (empty($incrementId)) {
                    $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
                }
                
                if (!empty($incrementId)) {
                    $orders[] = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                }
            }
            
            /** @var Mage_Sales_Model_Order $order */
            foreach ($orders as $order) {
                try {
                    if (!is_object($order) || $order->isEmpty()) {
                        continue;
                    }
                    
                    $this->logger->addLog("getIsSkipToSignifyd = {$order->getIsSkipToSignifyd()}");
                    if($order->getIsSkipToSignifyd()){
                        $this->logger->addLog("Avoid to sent to signifyd");
                        continue;
                    }
                    
                    //avoid teamwork order 
                    if($order->getCreateOrderMethod() != 0){
                        $this->logger->addLog("Avoid teamwork order sent to signifyd");
                        continue;
                    }
                    
                    $originStoreCode = $order->getData('origin_store_code');
                    if (empty($originStoreCode)) {
                        $request = Mage::app()->getFrontController()->getRequest();
                        
                        if (stripos($request->getRequestUri(), '/api/') === false) {
                            $order->setData('origin_store_code', Mage::app()->getStore()->getCode());
                        }
                    }
                    
                    if (is_null(Mage::registry('signifyd_action_' . $order->getIncrementId()))) {
                        Mage::register('signifyd_action_' . $order->getIncrementId(), 1); // Avoid recurssions
                    } else {
                        // Order already been processed, ignore it
                        continue;
                    }
                    
                    $eventName = $observer->getEvent()->getName();
                    $this->logger->addLog("Order {$order->getIncrementId()} state: {$order->getState()}, event: {$eventName}", $order);
                    
                    $result = $this->getHelper()->buildAndSendOrderToSignifyd($order, false, $updateOnly);
                    $this->logger->addLog("Create case result for " . $order->getIncrementId() . ": {$result}", $order);
                    
                    //PayPal express can't be put on hold before everything is processed or
                    //it won't send confirmation e-mail to customer
                    //Also there is no different status before the process is complete as is with PayFlow
                    $asyncHoldMethods = array('paypal_express', 'payflow_link', 'payflow_advanced');
                    if ($result == "sent" && !in_array($order->getPayment()->getMethod(), $asyncHoldMethods)) {
                        $this->putOrderOnHold($order);
                    }
                } catch (Exception $e) {
                    $incrementId = $order->getIncrementId();
                    $incrementId = empty($incrementId) ? '' : " $incrementId";
                    $this->logger->addLog("Failed to open case for order{$incrementId}: " . $e->__toString(), $order);
                }
                
                if (!is_null(Mage::registry('signifyd_action_' . $order->getIncrementId()))) {
                    Mage::unregister('signifyd_action_' . $order->getIncrementId()); // Avoid recurssions
                }
            }
        } catch (Exception $e) {
            $this->logger->addLog("Open case exception: " . $e->__toString());
        }
        
        // If we get here, then we have failed to create the case.
        return $this;
    }
}
