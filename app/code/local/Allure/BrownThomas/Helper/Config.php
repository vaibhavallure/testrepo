<?php
class Allure_BrownThomas_Helper_Config extends Mage_Core_Helper_Abstract
{


    const XML_PATH_ALERT_DEBUG_ENABLED = 'brownthomas/email_group/debug_enabled';
    const XML_PATH_MODULE_ENABLED = 'brownthomas/module_status/module_enabled';
    const XML_PATH_TIMEZONE = 'brownthomas/module_status/timezone';

    

    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }

    public function getModuleStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

    public function getTimeZone(){
        return Mage::getStoreConfig(self::XML_PATH_TIMEZONE);
    }


    /*------------sftp config-----------------------------------------------*/
    public function isEnabledSFTP(){
        return Mage::getStoreConfig('brownthomas/sftp_details/enabled');
    }
    public function getHostSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/host'));
    }
    public function getPasswordSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/password'));
    }
    public function getUsernameSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/username'));
    }
    public function getTimeoutSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/timeout'));
    }
    public function getLocationSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/location'));
    }
    public function getReadLocationSFTP(){
        return trim(Mage::getStoreConfig('brownthomas/sftp_details/read_location'));
    }
    /*------------------------------------------------------------------------*/


    /*------------Data File cron config-----------------------------------------------*/
    public function isEnabledDataFileCron(){
        return Mage::getStoreConfig('brownthomas/product_cron/enabled');
    }
    public function getHourDataFileCron(){
        return Mage::getStoreConfig('brownthomas/product_cron/hour');
    }
   /* public function getMinuteProductCron(){
        return Mage::getStoreConfig('brownthomas/product_cron/minute');
    }*/
    public function getContentTypePLU(){
        return Mage::getStoreConfig('brownthomas/product_cron/file_content');
    }
    /*------------------------------------------------------------------------*/


    /*------------stock cron config-----------------------------------------------*/
    public function isEnabledStockCron(){
        return Mage::getStoreConfig('brownthomas/stock_cron/enabled');
    }
    public function getHourStockCron(){
        return Mage::getStoreConfig('brownthomas/stock_cron/hour');
    }
   /* public function getMinuteStockCron(){
        return Mage::getStoreConfig('brownthomas/stock_cron/minute');
    }*/
    /*------------------------------------------------------------------------*/


    /*------------Enrichment cron config-----------------------------------------------*/
    public function isEnabledEnrichmentCron(){
        return Mage::getStoreConfig('brownthomas/enrichment_cron/enabled');
    }
    public function getHourEnrichmentCron(){
        return Mage::getStoreConfig('brownthomas/enrichment_cron/hour');
    }
   /* public function getMinuteEnrichmentCron(){
        return Mage::getStoreConfig('brownthomas/enrichment_cron/minute');
    }*/
    public function getContentTypePPC(){
        return Mage::getStoreConfig('brownthomas/enrichment_cron/file_content');
    }
    /*------------------------------------------------------------------------*/

}