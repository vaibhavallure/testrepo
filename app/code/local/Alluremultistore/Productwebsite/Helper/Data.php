<?php

class Alluremultistore_Productwebsite_Helper_Data extends Mage_Customer_Helper_Data
{
	const XML_PATH_PRODUCT_ASSIGN_CRON_STATUS = 'allure_productwebsite/product_website/enabled';
	const XML_PATH_CHANGE_PRODUCT_STATUS = 'allure_productwebsite/product_website/change_product_status';
	const XML_PATH_PRODUCT_STATUS = 'allure_productwebsite/product_website/product_status';
	const XML_PATH_DEBUG_STATUS = 'allure_productwebsite/product_website/debug';
	 
	public function getProductAssignCronStatus(){
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_ASSIGN_CRON_STATUS);
	}
	
	public function getChangeProductStatus(){
		return Mage::getStoreConfig(self::XML_PATH_CHANGE_PRODUCT_STATUS);
	}
	
	public function getProductStatus(){
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_STATUS);
	}
	
	public function getDebugStatus(){
		return Mage::getStoreConfig(self::XML_PATH_DEBUG_STATUS);
	}
}