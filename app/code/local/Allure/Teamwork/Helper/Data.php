<?php

class Allure_Teamwork_Helper_Data extends Mage_Core_Helper_Data
{
    const XML_TEAMWORK_STATUS       = "allure_teamwork/teamwork_settings/status";
    const XML_TEAMWORK_ACCESS_TOKEN = "allure_teamwork/teamwork_settings/access_token";
    
    public function getTeamworkStatus(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_STATUS);
    }
    
    public function getTeamworkAccessToken(){
        return Mage::getStoreConfig(self::XML_TEAMWORK_ACCESS_TOKEN);
    }
}
