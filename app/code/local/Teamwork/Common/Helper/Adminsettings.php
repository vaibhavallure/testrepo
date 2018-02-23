<?php
class Teamwork_Common_Helper_Adminsettings extends Mage_Core_Helper_Abstract
{
    const CHQ_API_OPTIONS_PATH = 'teamwork_common/chq/path';
    const CHQ_API_ACCESS_TOKEN = 'teamwork_common/chq/access_token';
    const CHQ_API_LOG_API = 'teamwork_common/chq/log_api';
    const CHQ_API_STYLE_PER_BUTCH = 'teamwork_common/chq_request/style_per_butch';
    const CHQ_API_PRICE_PER_BUTCH = 'teamwork_common/chq_request/price_per_butch';
    const CHQ_MAX_API_CHUNK_INTERVAL = 'teamwork_common/chq_request/max_api_chunk_interval';
    
    public $source = 'magento';
    
    public function getServerLink()
    {
        return rtrim(Mage::getStoreConfig(self::CHQ_API_OPTIONS_PATH), '/') . '/';
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function getAccessToken()
    {
        return Mage::getStoreConfig(self::CHQ_API_ACCESS_TOKEN);
    }
    
    public function writeLog()
    {
        return Mage::getStoreConfig(self::CHQ_API_LOG_API);
    }
    
    public function getEntitiesPerButch($configType=self::CHQ_API_STYLE_PER_BUTCH)
    {
        return Mage::getStoreConfig($configType);
    }
    
    public function getWaitApiTime()
    {
        return Mage::getStoreConfig(self::CHQ_MAX_API_CHUNK_INTERVAL);
    }
}