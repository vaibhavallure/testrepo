<?php
class Allure_HarrodsInventory_Helper_Config extends Mage_Core_Helper_Abstract
{


    const XML_PATH_ALERT_DEBUG_ENABLED = 'harrodsinventory/email_group/debug_enabled';
    const XML_PATH_MODULE_ENABLED = 'harrodsinventory/module_status/module_enabled';
    const XML_PATH_FILE_TYPE = 'harrodsinventory/email_group/filetype_enabled';
    const XML_PATH_TIMEZONE = 'harrodsinventory/module_status/timezone';

    

    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }

    public function getModuleStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }
    public function getFileType(){
        return Mage::getStoreConfig(self::XML_PATH_FILE_TYPE);
    }

    public function getTimeZone(){
        return Mage::getStoreConfig(self::XML_PATH_TIMEZONE);
    }


}