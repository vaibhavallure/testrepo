<?php

class Allure_PosInventory_Model_Observer
{
    public function updateInventoryByStock (Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $stockItem = $observer->getEvent()->getStockItem();
        
        //$observer->getEvent()->setStockItem($product->getStockItem());
        $observer->getStockItem()->setData($product->getStockItem()->getData());
        
    }
}