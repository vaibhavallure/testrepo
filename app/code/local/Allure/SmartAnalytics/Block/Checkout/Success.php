<?php
class Allure_SmartAnalytics_Block_Checkout_Success extends Mage_Core_Block_Template
{
    public function getOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        return Mage::getModel('sales/order')->load($orderId);
    }
}
