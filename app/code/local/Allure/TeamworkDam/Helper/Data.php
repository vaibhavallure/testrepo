<?php
class Allure_TeamworkDam_Helper_Data extends Mage_Core_Helper_Abstract{
    const XML_TEAMWORKDAM_STATUS               = "teamworkdam/module_status/module_enabled";
    const XML_TEAMWORKDAM_URL                  = "teamworkdam/module_status/base_url";
    const XML_TEAMWORKDAM_ACCESS_TOKEN         = "teamworkdam/module_status/access_token";


    public function getTeamworkDamStatus(){
        return Mage::getStoreConfig(self::XML_TEAMWORKDAM_STATUS);
    }

    public function getTeamworkDamUrl(){
        return Mage::getStoreConfig(self::XML_TEAMWORKDAM_URL);
    }

    public function getTeamworkDamAccessToken(){
        return Mage::getStoreConfig(self::XML_TEAMWORKDAM_ACCESS_TOKEN);
    }
}
