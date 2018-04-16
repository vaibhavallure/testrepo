<?php

class Allure_Teamwork_Helper_Data extends Mage_Core_Helper_Data
{
    const XML_TEAMWORK_STATUS               = "allure_teamwork/teamwork_settings/status";
    const XML_TEAMWORK_ACCESS_TOKEN         = "allure_teamwork/teamwork_settings/access_token";
    
    const XML_TEAMWORK_BASE_URL             = "allure_teamwork/teamwork_settings/teamwork_url";
    const XML_NEXT_QUERY_SYNC_TIME          = "allure_teamwork/teamwork_settings/last_sync_query_time";
    const XML_TEAMWORK_PAGE_LIMIT           = "allure_teamwork/teamwork_settings/page_limit";
    
    const XML_TEAMWORK_LOG_STATUS           = "allure_teamwork/teamwork_settings/log_status";
    
    const SYNC_TEAMWORK_CUSTOMER_URLPATH    = "/customers/listmodified";
    const UPADTE_CUSTOMER_URLPATH           = "/customers/update";
    
    const SYNC_TM_MAG_LOG_FILE              = "sync_teamwork_customer.log";
    
    /**
     * @return teamwork module status
     */
    public function getTeamworkStatus(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_STATUS);
    }
    
    /**
     * @return teamwork access token
     */
    public function getTeamworkAccessToken(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_ACCESS_TOKEN);
    }
    
    /**
     * get teamwork home url path
     */
    public function getTeamworkUrl(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_BASE_URL);
    }
    
    /**
     * get last sync query time
     */
    public function getLastSyncQueryTime(){
        return Mage::getStoreConfig(self::XML_NEXT_QUERY_SYNC_TIME);
    }
    
    /**
     * get teamwork page limit 
     */
    public function getTeamworkPageLimit(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_PAGE_LIMIT);
    }
    
    /**
     * get log status 
     */
    public function getLogStatus(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_LOG_STATUS);
    }
}
