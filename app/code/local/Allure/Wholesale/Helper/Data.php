<?php
class Allure_Wholesale_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_MODULE_ENABLED = 'wholesale/module_status/module_enabled';
    const XML_PATH_STORE = 'wholesale/module_status/store_id';
    const XML_PATH_TEMPLATE_ID = 'wholesale/module_status/email_temp_code';
    const XML_PATH_RECEIVER = 'wholesale/module_status/receiver';


    public function getStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

    public function getStoreId(){
        return Mage::getStoreConfig(self::XML_PATH_STORE);
    }
    public function getTemplateId(){
        return Mage::getStoreConfig(self::XML_PATH_TEMPLATE_ID);
    }
    public function getEmailReceiver(){
        return Mage::getStoreConfig(self::XML_PATH_RECEIVER);
    }

    public function isWholesaleStore()
    {
        return $this->getCurrentStoreId()==$this->getStoreId();
    }

    public function getCurrentStoreId()
    {
        $store = Mage::app()->getStore();
        return $store->getId();
    }
}
