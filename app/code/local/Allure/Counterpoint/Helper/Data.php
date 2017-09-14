<?php
/**
 * @author:Allure
 * 
 */
class Allure_Counterpoint_Helper_Data extends Mage_Core_Helper_Abstract{
    //Counter point settings info
    const XML_COUNTERPOINT_HOST_NAME 	= 'allure_counterpoint/counterpoint_settings/host';
    const XML_COUNTERPOINT_DB_USERNAME 	= 'allure_counterpoint/counterpoint_settings/username';
    const XML_COUNTERPOINT_DB_PASSWORD 	= 'allure_counterpoint/counterpoint_settings/passwd';
    
    const XML_COUNTERPOINT_STORE_ID 	= 'allure_counterpoint/counterpoint_settings/cp_store';
    
    //SugarCRM settings info
    const XML_SUGERCRM_STATUS           = 'allure_counterpoint/crm_settings/status';
    const XML_SUGERCRM_API_URL          = 'allure_counterpoint/crm_settings/api_url';
    const XML_SUGERCRM_USERNAME         = 'allure_counterpoint/crm_settings/username';
    const XML_SUGARCRM_PASSWORD         = 'allure_counterpoint/crm_settings/passwd';
    const XML_SUGARCRM_CLIENT_ID        = 'allure_counterpoint/crm_settings/client_id';
    const XML_SUGARCRM_CLIENT_SECRET    = 'allure_counterpoint/crm_settings/client_secret';
    const XML_SUGARCRM_GRANT_TYPE       = 'allure_counterpoint/crm_settings/grant_type';
    const XML_SUGARCRM_PLATFORM         = 'allure_counterpoint/crm_settings/platform';
    const XML_SUGARCRM_DEBUG_LOG        = 'allure_counterpoint/crm_settings/debug_log';
    
    //SugarCRM api path
    const LOGIN_PATH             = '/rest/v10/oauth2/token';
    const RETRIVE_CUSTOMERS_PATH = '/rest/v10/Contacts/filter';
    const CUSTOMER_SEARCH_PATH   = '/rest/v10/Contacts/filter'; 
    
    const ADD_CUSTOMER_PATH      = '/rest/v10/Contacts';
    
    /**
     * Counter Point
     * Get counter point server name i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->counter point settings
     */
    public function getHostName(){
        return Mage::getStoreConfig(self::XML_COUNTERPOINT_HOST_NAME);
    }
    
    /**
     * Counter Point
     * Get counter point database username i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->counter point settings
     */
    public function getDBUserName(){
        return Mage::getStoreConfig(self::XML_COUNTERPOINT_DB_USERNAME);
    }
    
    /**
     * Counter Point
     * Get counter point database password i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->counter point settings
     */
    public function getDBPassword(){
        return Mage::getStoreConfig(self::XML_COUNTERPOINT_DB_PASSWORD);
    }
    
    /**
     * get store id for where to store counterpoint data
     * @path : admin->configuration->universal bridge->counter point settings
     */
    public function getCounterPointStoreId(){
        return Mage::getStoreConfig(self::XML_COUNTERPOINT_STORE_ID);
    }
    
    
    /**
     * Suagr CRM
     * Get status i.e it's enabled or disabled
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMStatus(){
        return Mage::getStoreConfig(self::XML_SUGERCRM_STATUS);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm api url i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMApiUrl(){
        return Mage::getStoreConfig(self::XML_SUGERCRM_API_URL);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm username i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMUsername(){
        return Mage::getStoreConfig(self::XML_SUGERCRM_USERNAME);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm password i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMPassword(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_PASSWORD);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm client id i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMClientId(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_CLIENT_ID);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm client secret i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMClientSecret(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_CLIENT_SECRET);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm grant type i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMGrantType(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_GRANT_TYPE);
    }
    
    /**
     * Sugar CRM
     * Get sugarcrm platform i.e saved in magento admin configuration.
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMPlatform(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_PLATFORM);
    }
    
    /**
     * Sugar CRM
     * Get Printable log status
     * @path : admin->configuration->universal bridge->sugar crm settings
     */
    public function getSugarCRMDebugLogStatus(){
        return Mage::getStoreConfig(self::XML_SUGARCRM_DEBUG_LOG);
    }
}
