<?php

class Allure_AuthorizeNetApplePay_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($key)
    {
        return Mage::getStoreConfig('payment/authnetapplepay/'.$key);
    }
    
    public function isEnabled()
    {
        $authnetcim = Mage::getSingleton('authnetcim/method');
        return $this->isModuleEnabled() && $authnetcim->isAcceptJsEnabled() && $this->getConfig('active');
    }
    
    public function isEnabledOnFrontEnd()
    {
        return !Mage::app()->getStore()->isAdmin() && $this->isEnabled();
    }
}
