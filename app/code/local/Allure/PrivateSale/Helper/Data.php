<?php
class Allure_PrivateSale_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled(){
        return Mage::getStoreConfig("privatesale/module/module_enabled");
    }

    public function getCategory(){
        return Mage::getStoreConfig("privatesale/module/category");
    }

    public function getUsername(){
        return Mage::getStoreConfig("privatesale/credential/username");
    }

    public function getPassword(){
        return Mage::getStoreConfig("privatesale/credential/password");
    }
    public function hidePrivateCategory(){
        return Mage::getStoreConfig("privatesale/module/hide_category");
    }
}
