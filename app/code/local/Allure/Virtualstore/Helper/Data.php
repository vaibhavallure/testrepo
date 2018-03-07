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
    
    /**
     * get name of virtual store for order view in admin
     */
    public function getOrderStoreName($order)
    {
        if ($order) {
            $storeId    = $order->getStoreId();
            $oldStoreId = $order->getOldStoreId();
            $oldStore   = Mage::getSingleton("core/store")->load($oldStoreId);
            if (!$oldStore->getStoreId()) {
                $oldStore   = Mage::getSingleton("allure_virtualstore/store")->load($oldStoreId);
                $deleted    = Mage::helper('adminhtml')->__(' [deleted]');
                return nl2br($oldStore->getName()) . $deleted;
            }else{
                $store = Mage::app()->getStore($storeId);
                if($oldStore->getStoreId()){
                    $store = $oldStore;
                }
                $name = array(
                    $store->getWebsite()->getName(),
                    $store->getGroup()->getName(),
                    $store->getName()
                );
                return implode('<br/>', $name);
            }
        }
        return null;
    }
}
