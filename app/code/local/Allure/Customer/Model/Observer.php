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
   
}
