<?php

class Allure_GeoShipping_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getConfig ($key)
    {
        return Mage::getStoreConfig(
                'allure_geolocation/shipping_calculation/' . $key);
    }

    public function isEnabled ()
    {
        return $this->isModuleEnabled() && $this->getConfig('status') &&
                 Mage::helper('allure_geolocation')->isEnabled();
    }

    public function isEnabledOnFrontEnd ()
    {
        return ! Mage::app()->getStore()->isAdmin() && $this->isEnabled();
    }

    public function ignoreCustomerDefault ()
    {
        return $this->getConfig('ignore_default');
    }

    public function getDebugMode ()
    {
        return $this->getConfig('debug');
    }
}