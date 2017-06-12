<?php

class Allure_AdminPermissions_Block_Rewrite_Adminhtml_Permissions_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        
        $adminPermissionHelper = Mage::helper("allure_adminpermissions");
        if($adminPermissionHelper->checkStoreAdmin()){
        	$this->setTemplate('allure/adminpermissions/store/switcher.phtml');
        }else{
        	$this->setTemplate('store/switcher.phtml');
	        $this->setDefaultStoreName($this->__('All Store Views'));
        }
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
    }
   

    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
    	//Mage::log("hiiii",Zend_log::DEBUG,'abc',true);
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        
        $user = Mage::getSingleton('admin/session')->getUser();
        $websitesArr = array();
        if($user->getStoreRestrictions()){
        	$storeRestrictions = explode(',', $user->getStoreRestrictions());
        	foreach ($storeRestrictions as $storeId){
        		$websiteId= Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        		$websitesArr[] = $websiteId;
        	}
        	foreach ($websites as $websiteId => $website) {
        		if (!in_array($websiteId, $websitesArr)) {
        			unset($websites[$websiteId]);
        		}
        	}
        }
        
        return $websites;
    }

}
