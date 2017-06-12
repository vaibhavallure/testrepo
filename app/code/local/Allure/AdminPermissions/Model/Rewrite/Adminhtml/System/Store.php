<?php

class Allure_AdminPermissions_Model_Rewrite_Adminhtml_System_Store extends Mage_Adminhtml_Model_System_Store
{
    
    /**
     * Load/Reload Website collection
     *
     * @return array
     */
    protected function _loadWebsiteCollection()
    {
        $this->_websiteCollection = Mage::app()->getWebsites();
        $user = Mage::getSingleton('admin/session')->getUser();
        $websiteArr = array();
       
        $adminPermissionHelper = Mage::helper("allure_adminpermissions");
        if($adminPermissionHelper->checkStoreAdmin()){
        	//if($user->getStoreRestrictions()){
        		$storeRestrictions = explode(',', $user->getStoreRestrictions());
        		foreach ($storeRestrictions as $storeId){
        			$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        			$websiteArr[] = $websiteId;
        		}
        		 
        		foreach ($this->_websiteCollection as $websiteId => $website) {
        			if (!in_array($websiteId, $websiteArr)) {
        				unset($this->_websiteCollection[$websiteId]);
        			}
        		}
        	//}
        }
        
        return $this;
    }

    /**
     * Load/Reload Group collection
     *
     * @return array
     */
    protected function _loadGroupCollection()
    {
        $this->_groupCollection = array();
        $user = Mage::getSingleton('admin/session')->getUser();
        $websites = Mage::app()->getWebsites();
        $adminPermissionHelper = Mage::helper("allure_adminpermissions");
        if($adminPermissionHelper->checkStoreAdmin()){
        	$storeRestrictions = explode(',', $user->getStoreRestrictions());
        	foreach ($storeRestrictions as $storeId){
        		$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        		$websiteArr[] = $websiteId;
        	}
        	foreach ($websites as $websiteId => $website) {
        		if (!in_array($websiteId, $websiteArr)) {
        			unset($websites[$websiteId]);
        		}
        	}
        }
        
        
        foreach ($websites as $website) {
            foreach ($website->getGroups() as $group) {
                $this->_groupCollection[$group->getId()] = $group;
            }
        }
        return $this;
    }

    /**
     * Load/Reload Store collection
     *
     * @return array
     */
    protected function _loadStoreCollection()
    {
        $this->_storeCollection = Mage::app()->getStores();
        $user = Mage::getSingleton('admin/session')->getUser();
        $adminPermissionHelper = Mage::helper("allure_adminpermissions");
        if($adminPermissionHelper->checkStoreAdmin()){
        	$storeRestrictions = explode(',', $user->getStoreRestrictions());
        	foreach ($this->_storeCollection as $storeId => $store){
        		if (!in_array($storeId, $storeRestrictions)) {
        			unset($this->_storeCollection[$storeId]);
        		}
        	}
        	
        }
        return $this;
    }

  }
