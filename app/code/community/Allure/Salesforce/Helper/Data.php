<?php
/**
 * Allure-Salesforce Integration
 * @author aws02
 *
 */
class Allure_Salesforce_Helper_Data extends Mage_Core_Helper_Abstract{
    const XML_PATH_SALESFORCE_ENABLED           = "allure_salesforce/general/enabled";
    const XML_PATH_SALESFORCE_HOST              = "allure_salesforce/general/host";
    const XML_PATH_SALESFORCE_GRANT_TYPE        = "allure_salesforce/general/grant_type";
    const XML_PATH_SALESFORCE_CLIENT_ID         = "allure_salesforce/general/client_id";
    const XML_PATH_SALESFORCE_CLIENT_SECRET     = "allure_salesforce/general/client_secret";
    const XML_PATH_SALESFORCE_USERNAME          = "allure_salesforce/general/username";
    const XML_PATH_SALESFORCE_PASSWORD          = "allure_salesforce/general/password";
    const XML_PATH_GUEST_ACCOUNT                = "allure_salesforce/general/guest_account";
    const XML_PATH_GENERAL_PRICEBOOK            = "allure_salesforce/general/general_pricebook";
    const XML_PATH_WHOLESALE_PRICEBOOK          = "allure_salesforce/general/wholesale_pricebook";
    const XML_PATH_BULK_UPDATE_TIME             = "allure_salesforce/general/bulk_update_time";
    const XML_PATH_BULK_UPDATE_INITIAL_TIME     = "allure_salesforce/general/bulk_update_initial_time";
    
    public function isEnabled(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_ENABLED);
    }
    
    public function getHost(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_HOST);
    }
    
    public function getGrantType(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_GRANT_TYPE);
    }
    
    public function getClientId(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CLIENT_ID);
    }
    
    public function getClientSecret(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CLIENT_SECRET);
    }
    
    public function getUsername(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_USERNAME);
    }
    
    public function getPassword(){
        return Mage::getStoreConfig(self::XML_PATH_SALESFORCE_PASSWORD);
    }
    
    public function getGuestAccount(){
        return Mage::getStoreConfig(self::XML_PATH_GUEST_ACCOUNT);
    }
    
    public function getGeneralPricebook(){
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_PRICEBOOK);
    }
    
    public function getWholesalePricebook(){
        return Mage::getStoreConfig(self::XML_PATH_WHOLESALE_PRICEBOOK);
    }

    public function getLastRunTime() {
        return Mage::getStoreConfig(self::XML_PATH_BULK_UPDATE_TIME);
    }

    public function setLastRunTime($lastRunTime) {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_BULK_UPDATE_TIME,$lastRunTime)->cleanCache();
    }

    public function  getBulkUpdateInitialTime() {
        return Mage::getStoreConfig(self::XML_PATH_BULK_UPDATE_INITIAL_TIME);
    }
}
