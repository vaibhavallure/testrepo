<?php
/**
 * @author allure
 */
class Allure_Virtualstore_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_websiteCollection   = array();
    protected $_groupCollection     = array();
    protected $_storeCollection     = array();
    
    /**
    * return virtual store array
    */
    public function getVirtualStores(){
        $stores = Mage::getSingleton("allure_virtualstore/store")->getCollection();
        foreach ($stores as $store) {
            if ($store->getId() == 0) {
                continue;
            }
            $this->_storeCollection[$store->getId()] = $store;
        }
        return $this->_storeCollection;
    }
    
    /**
     * return virtual group array
     */
    public function getVirtualGroups(){
        $groups = Mage::getSingleton("allure_virtualstore/group")
            ->getCollection();
        foreach ($groups as $group) {
            if ($group->getId() == 0) {
                continue;
            }
            $this->_groupCollection[$group->getId()] = $group;
        }
        return $this->_groupCollection;
    }
    
    /**
     * return virtual website array
     */
    public function getVirtualWebsites(){
        $websites = Mage::getSingleton("allure_virtualstore/website")
            ->getCollection();
        foreach ($websites as $website) {
            if ($website->getId() == 0) {
                continue;
            }
            $this->_websiteCollection[$website->getId()] = $website;
        }
        return $this->_websiteCollection;
    }
}
