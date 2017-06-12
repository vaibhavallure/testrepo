<?php

class Allure_GeoStore_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getConfig ($key)
    {
        return Mage::getStoreConfig('allure_geolocation/switch_store/' . $key);
    }

    public function isEnabled ()
    {
        return $this->isModuleEnabled() && $this->getConfig('status') &&
                 Mage::helper('allure_geolocation')->isEnabled();
    }

    public function getMapping ()
    {
        return $this->getConfig('mapping');
    }

    public function getMappingArray ()
    {
        return unserialize($this->getMapping());
    }

    public function getDebugMode ()
    {
        return $this->getConfig('debug');
    }
}