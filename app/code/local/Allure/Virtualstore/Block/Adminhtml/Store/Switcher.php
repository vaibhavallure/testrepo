<?php

class Allure_Virtualstore_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('allure/virtualstore/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }
    
    public function getVirtualStores(){
        return Mage::helper("allure_virtualstore")->getVirtualStores();
    }
}
