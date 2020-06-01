<?php

class Allure_Orders_Model_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    const GUEST = 0;
    const GENERAL = 1;
    const WHOLESALE = 2;
    
    /**
     * {@inheritDoc}
     * @see Mage_Core_Model_Abstract::save()
     */
    public function save()
    {
        try {
            parent::save();
            
            $order = $this->getOrder();
            $isSent = $order->getEmailSent();
            $storeId = $order->getStoreId();
            $isSendOrderEmail = Mage::helper("allure_orders")
                ->canSendConfirmationEmail($storeId);
           
            $customerGroupId = $order->getCustomerGroupId();
            
            $paymentMethod = $order->getPayment()->getMethod();
            Mage::log("order_id = {$order->getId()} payment method = {$paymentMethod}",Zend_Log::DEBUG, 'split_orders.log',true);
            
            if($isSendOrderEmail && !$isSent && $paymentMethod != "paypal_express"){
                if($customerGroupId == self::GUEST){
                    $order->queueNewOrderEmail();
                }elseif ($customerGroupId == self::GENERAL){
                    $orderArray = array($order->getId() => $order);
                    $order->queueMultiAddressNewOrderEmail($orderArray);
                }else {
                    $order->queueNewOrderEmail();
                }
            }
            
            
        } catch (Exception $e) {
            throw  $e;
        }
        
        /* try {
            //split order
            $order = $this->getOrder();
            Mage::getModel("allure_orders/splitOrder")->spliteOrders($order->getId(),$order->getIncrementId());
        } catch (Exception $e) {
            Mage::log("Exception in save method of invoice",Zend_Log::DEBUG, 'split_orders.log',true);
            Mage::log("order_id = {$order->getId()} Exc = {$e->getMessage()}",Zend_Log::DEBUG, 'split_orders.log',true);
            
        } */
        
        return $this;
    }
}
