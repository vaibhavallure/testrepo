<?php
class Allure_Orders_Model_Teamwork_Webstaging extends Teamwork_TransferMariatash_Model_Webstaging
{
    public function activate($observer)
    {
        $fileName = "split_orders.log";
        $order = $observer->getEvent()->getOrder();   
        Mage::log("In activate method", 7, $fileName, true);
        Mage::log("ORDER ID = {$order->getId()}", 7, $fileName, true);
        if($order->getIsProcessed()){
            return parent::activate($observer);
        }
        return $this;
    }
}