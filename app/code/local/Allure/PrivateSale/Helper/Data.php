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
    public function hideFilter(){
        return Mage::getStoreConfig("privatesale/module/hide_filter");
    }
    public function filterHide()
    {
        $currentCategory = Mage::registry('current_category');

        if($currentCategory->getId()==$this->getCategory())
        {
            if($this->hideFilter())
            {
                return true;
            }
        }

        return false;
    }
}
