<?php
class Allure_HarrodsInventory_Helper_Config extends Mage_Core_Helper_Abstract
{


    const XML_PATH_ALERT_DEBUG_ENABLED = 'harrodsinventory/email_group/debug_enabled';
    const XML_PATH_MODULE_ENABLED = 'harrodsinventory/module_status/module_enabled';
    const XML_PATH_TIMEZONE = 'harrodsinventory/module_status/timezone';

    

    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }
    public function getDebugEmails(){
        return Mage::getStoreConfig('harrodsinventory/email_group/group_emails');
    }
    /*public function getDebugEmailsTemp(){
        return Mage::getStoreConfig('harrodsinventory/email_group/email_template');
    }*/

    public function getModuleStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

    public function getTimeZone(){
        return Mage::getStoreConfig(self::XML_PATH_TIMEZONE);
    }


    /*------------sftp config-----------------------------------------------*/
    public function isEnabledSFTP(){
        return Mage::getStoreConfig('harrodsinventory/sftp_details/enabled');
    }
    public function getHostSFTP(){
        return trim(Mage::getStoreConfig('harrodsinventory/sftp_details/host'));
    }
    public function getPasswordSFTP(){
        return trim(Mage::getStoreConfig('harrodsinventory/sftp_details/password'));
    }
    public function getUsernameSFTP(){
        return trim(Mage::getStoreConfig('harrodsinventory/sftp_details/username'));
    }
    public function getTimeoutSFTP(){
        return trim(Mage::getStoreConfig('harrodsinventory/sftp_details/timeout'));
    }
    public function getLocationSFTP(){
        return trim(Mage::getStoreConfig('harrodsinventory/sftp_details/location'));
    }
    /*------------------------------------------------------------------------*/


    /*------------product cron config-----------------------------------------------*/
    public function isEnabledProductCron(){
        return Mage::getStoreConfig('harrodsinventory/product_cron/enabled');
    }
    public function getHourProductCron(){
        return Mage::getStoreConfig('harrodsinventory/product_cron/hour');
    }
   /* public function getMinuteProductCron(){
        return Mage::getStoreConfig('harrodsinventory/product_cron/minute');
    }*/
   /* public function getContentTypePLU(){
        return Mage::getStoreConfig('harrodsinventory/product_cron/file_content');
    }*/
    /*------------------------------------------------------------------------*/


    /*------------stock cron config-----------------------------------------------*/
    public function isEnabledStockCron(){
        return Mage::getStoreConfig('harrodsinventory/stock_cron/enabled');
    }
    public function getHourStockCron(){
        return Mage::getStoreConfig('harrodsinventory/stock_cron/hour');
    }
   /* public function getMinuteStockCron(){
        return Mage::getStoreConfig('harrodsinventory/stock_cron/minute');
    }*/
    /*------------------------------------------------------------------------*/


    /*------------stock cron config-----------------------------------------------*/
    public function isEnabledPriceCron(){
        return Mage::getStoreConfig('harrodsinventory/price_cron/enabled');
    }
    public function getHourPriceCron(){
        return Mage::getStoreConfig('harrodsinventory/price_cron/hour');
    }
   /* public function getMinutePriceCron(){
        return Mage::getStoreConfig('harrodsinventory/price_cron/minute');
    }*/
   /* public function getContentTypePPC(){
        return Mage::getStoreConfig('harrodsinventory/price_cron/file_content');
    }*/
    /*------------------------------------------------------------------------*/

}