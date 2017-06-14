<?php

class Allure_Counterpoint_Helper_Data extends Mage_Core_Helper_Abstract{
    const COUNTERPOINT_HOST_NAME 	= 'allure_counterpoint/setting/host';
    const COUNTERPOINT_DB_USERNAME 	= 'allure_counterpoint/setting/username';
    const COUNTERPOINT_DB_PASSWORD 	= 'allure_counterpoint/setting/passwd';
    
    public function getHostName(){
    	return Mage::getStoreConfig(self::COUNTERPOINT_HOST_NAME);
    }
    
    public function getDBUserName(){
    	return Mage::getStoreConfig(self::COUNTERPOINT_DB_USERNAME);
    }
    
    public function getDBPassword(){
    	return Mage::getStoreConfig(self::COUNTERPOINT_DB_PASSWORD);
    }
}
