<?php

class Allure_Xero_Helper_Data extends Mage_Core_Helper_Data
{
    const XML_XERO_API_STATUS               = "allure_xero/xero_api/status";
    const XML_XERO_BASE_API_URL         = "allure_xero/xero_api/base_api_url";
    const XML_XERO_TOKEN_URL             = "allure_xero/xero_api/token_url";
    const XML_XERO_REDIRECT_URI          = "allure_xero/xero_api/redirect_uri";
    const XML_XERO_AUTH_CODE          = "allure_xero/xero_api/auth_code";

    const XML_XERO_ACCESS_TOKEN          = "allure_xero/xero_api/access_token";
    const XML_XERO_REFRESH_TOKEN          = "allure_xero/xero_api/refresh_token";

    const XML_XERO_LOG_STATUS          = "allure_xero/xero_api/log_status";
    const XML_XERO_LOG_FILE          = "allure_xero/xero_api/log_file";

    public function getXeroStatus(){
        return Mage::getStoreConfig(self::XML_XERO_API_STATUS);
    }

    public function getXeroBaseApiUrl(){
        return Mage::getStoreConfig(self::XML_XERO_BASE_API_URL);
    }

    public function getXeroTokenUrl(){
        return Mage::getStoreConfig(self::XML_XERO_TOKEN_URL);
    }

    public function getRedirectUri(){
        return Mage::getStoreConfig(self::XML_XERO_REDIRECT_URI);
    }

    public function getAuthCode(){
        return Mage::getStoreConfig(self::XML_XERO_AUTH_CODE);
    }

    public function getAccessToken(){
        return Mage::getStoreConfig(self::XML_XERO_ACCESS_TOKEN);
    }

    public function getRefreshToken(){
        return Mage::getStoreConfig(self::XML_XERO_REFRESH_TOKEN);
    }

    public function setRefreshToken($refreshToken){
        Mage::getModel('core/config')->saveConfig(self::XML_XERO_REFRESH_TOKEN,$refreshToken)->cleanCache();
    }

    public function getLogStatus(){
        return Mage::getStoreConfig(self::XML_XERO_LOG_STATUS);
    }

    public function log($logContent) {
        $logFileName = Mage::getStoreConfig(self::XML_XERO_LOG_FILE);
        if($this->getLogStatus()){
            if(isset($logFileName))
                Mage::log($logContent,Zend_Log::DEBUG,$logFileName,true);
        }
    }
}
