<?php
class Allure_Wholesale_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_MODULE_ENABLED = 'wholesale/module_status/module_enabled';
    const XML_PATH_STORE = 'wholesale/module_status/store_id';


    public function getStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

    public function getStoreId(){
        return Mage::getStoreConfig(self::XML_PATH_STORE);
    }
}
