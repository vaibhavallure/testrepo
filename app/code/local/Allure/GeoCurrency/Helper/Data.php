<?php
class Allure_GeoCurrency_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($key)
    {        
        return Mage::getStoreConfig('allure_geolocation/switch_currency/'.$key);
    }
    
    public function isEnabled()
    {
        return $this->isModuleEnabled() && $this->getConfig('status') && Mage::helper('allure_geolocation')->isEnabled();
    }
    
    public function isEnabledOnFrontEnd()
    {
    	return !Mage::app()->getStore()->isAdmin() && $this->isEnabled();
    }
    
    public function getMapping()
    {
        return $this->getConfig('mapping');
    }
    
    public function getMappingArray()
    {
    	return unserialize($this->getMapping());
    }
    
    public function getDebugMode()
    {
        return $this->getConfig('debug');
    }
    public function getCountryCodeByCurrencyCode($currencyCode)
    {
        if($currencyCode=="USD")
            return "US";

        $mapping=$this->getMappingArray();
        foreach ($mapping as $mp)
        {
            if($mp["currencyCode"]==$currencyCode)
            {
                return $mp["countryCode"];
            }
        }

        return false;

    }
    public function getCountryNameByCurrencyCode($currencyCode)
    {
        $countryCode=$this->getCountryCodeByCurrencyCode($currencyCode);
        return Mage::app()->getLocale()->getCountryTranslation($countryCode);
    }
}