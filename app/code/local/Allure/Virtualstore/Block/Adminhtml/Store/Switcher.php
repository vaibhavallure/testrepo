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
    
    public function getVirtualWebsites(){
        return Mage::helper("allure_virtualstore")->getVirtualWebsites();
    }
    
    public function getGroups($websiteId){
        $groups = Mage::getSingleton("allure_virtualstore/group")->getCollection()
        ->addFieldToFilter('website_id',$websiteId);
        return $groups;
    }
    
    public function getStores($groupId){
        $stores = Mage::getSingleton("allure_virtualstore/store")->getCollection()
        ->addFieldToFilter('group_id',$groupId);
        return $stores;
    }
    
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
    
    public function isShow()
    {
        return true; //!Mage::app()->isSingleStoreMode();
    }
}
