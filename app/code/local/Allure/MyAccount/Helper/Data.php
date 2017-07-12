<?php

class Allure_MyAccount_Helper_Data extends Mage_Customer_Helper_Data
{
	const STORE_COLOR_MAPPING_XML = "myaccount/general/storemapping";
    public function getStoreColorConfig(){
    	$storeColorConfig = Mage::getStoreConfig(self::STORE_COLOR_MAPPING_XML);
    	$config=unserialize($storeColorConfig);
    	$storeConf = array();
    	foreach ($config as $conf){
    		$storeConf[$conf['store']] = $conf;
    	}
    	return $storeConf;
    }
}
