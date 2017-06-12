<?php

class Allure_Varnishgento_Model_Observer extends Opsway_Varnishgento_Model_Observer
{
    /**
     * Clean cache for specific product ids list
     * @param Varien_Event_Observer $observer
     */
    public function cleanCacheByProductIds($observer){
        if (!$this->_isActive()) {
            return;
        }
        $product_ids = $observer->getEvent()->getProductIds();
        if(is_null($product_ids)){
        	$product_id = $observer->getEvent()->getProduct()->getId();
        	$product_ids = array($product_id);
        }
        Mage::log($product_ids,Zend_log::DEBUG,'abc',true);
        Mage::helper('opsway_varnishgento')->refreshCacheForProduct($product_ids);
    }

}
