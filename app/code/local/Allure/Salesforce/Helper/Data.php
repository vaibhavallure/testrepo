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
}
