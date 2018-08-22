<?php
class Allure_MultiCurrency_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($key)
    {
        return Mage::getStoreConfig('multicurrency/currency_attr/'.$key);
    }

    public function isEnabled()
    {
        return $this->isModuleEnabled() && $this->getConfig('status') && Mage::helper('multicurrency')->isEnabled();
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
}
	 