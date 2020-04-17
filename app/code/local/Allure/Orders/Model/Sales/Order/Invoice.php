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
            Mage::log("In save method of invoice", Zend_Log::DEBUG, 'abc.log', true);
            
            $order = $this->getOrder();
            $isSent = $order->getEmailSent();
            $storeId = $order->getStoreId();
            $isSendOrderEmail = Mage::helper("allure_orders")
                ->canSendConfirmationEmail($storeId);
           
            $customerGroupId = $order->getCustomerGroupId();
            
            if($isSendOrderEmail && !$isSent){
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
        return $this;
    }
}
