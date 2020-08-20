<?php
/**
 * 
 * @author allure
 *
 */
class Allure_Gtm_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_GTM_ENABLED       = 'allure_gtm/general/enabled';
    const XML_GTM_HEAD_SCRIPT   = 'allure_gtm/general/head_script';
    const XML_GTM_BODY_SCRIPT   = 'allure_gtm/general/body_script';
    
    public function isGtmEnabled()
    {
        return Mage::getStoreConfig(self::XML_GTM_ENABLED);
    }
    
    public function getHeadScript()
    {
        if(!$this->isGtmEnabled()) return "";
        return Mage::getStoreConfig(self::XML_GTM_HEAD_SCRIPT);
    }
    
    public function getBodyScript()
    {
        if(!$this->isGtmEnabled()) return "";
        return Mage::getStoreConfig(self::XML_GTM_BODY_SCRIPT);
    }
}