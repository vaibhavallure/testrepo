<?php
class Allure_CustomerLoginMonitor_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DEBUG_ENABLED = 'customerloginmonitor/debug/debug_enabled';
    const XML_PATH_MODULE_ENABLED = 'customerloginmonitor/module_status/module_enabled';
    const XML_PATH_LOG = 'customerloginmonitor/module_status/log';


    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_DEBUG_ENABLED);
    }
    public function getLogOf(){
        return Mage::getStoreConfig(self::XML_PATH_LOG);
    }
    public function getModuleStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

}
