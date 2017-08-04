<?php

class Allure_Customer_Model_Observer
{
    public function saveDefaultCustomerWebsite(Varien_Event_Observer $observer)
    {
    	$websiteId = Mage::getStoreConfig('customer/create_account/customer_default_website');
    	$storeId = Mage::app()
    	->getWebsite($websiteId)
    	->getDefaultGroup()
    	->getDefaultStoreId();
    	$attr = Mage::getModel('eav/entity_attribute')->load('website_id', 'attribute_code');
    	$attr->setDefaultValue($websiteId);
    	$attr->save();
    	
    	$attrStore = Mage::getModel('eav/entity_attribute')->load('store_id', 'attribute_code');
    	$attrStore->setDefaultValue($storeId);
    	$attrStore->save();
    	return ;
    }
    public function setcustomerStatusActive(){
    	Mage::log("Setting Customer active",Zend_log::DEBUG,'customer_activate',true);
    	$customerCollection = Mage::getModel('customer/customer')->getCollection();
    	$customerCollection->addFieldToFilter( 'am_is_activated', '1' );
    	if(count($customerCollection)){
    		foreach ($customerCollection as $customer){
    			$customer = Mage::getModel('customer/customer')->load($customer->getId());
    			$customer->setAmIsActivated(2);
    			$customer->save();
    		}
    	}
    	
    }
   
}
