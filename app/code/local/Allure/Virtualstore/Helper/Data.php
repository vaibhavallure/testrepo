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
        $stores->setOrder('sort_order', 'asc');
        $stores->setOrder('store_id', 'asc');
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
         $websites->setOrder('sort_order', 'asc');
         $websites->setOrder('website_id', 'asc');
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
                $oldWebsite = Mage::getSingleton("allure_virtualstore/website")->load($oldStore->getWebsiteId());
                $oldGroup   = Mage::getSingleton("allure_virtualstore/group")->load($oldStore->getGroupId());
                $name = array(
                    $oldWebsite->getName(),
                    $oldGroup->getName(),
                    $oldStore->getName()
                );
                return implode('<br/>', $name);
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

    /**
     * get store name by using store id
     */
    public function getStoreName($storeId){
        $store = Mage::getSingleton("core/store")->load($storeId);
        if (!$store->getStoreId()) {
            $store = Mage::getSingleton("allure_virtualstore/store")->load($storeId);
        }
        return $store->getName();
    }

    /**
     * get store name by using store id
     */
    public function getStoreCode($storeId){
        $store = Mage::getSingleton("allure_virtualstore/store")->load($storeId);

        return $store->getCode();
    }

    /**
     * get store name by using store id
     */
    public function getStoreId($storeCode = NULL){
        $store = Mage::getSingleton("allure_virtualstore/store")->load($storeCode, 'code');

        return $store->getId();
    }


    public function getGroupId($storeid){
        $store = Mage::getSingleton("allure_virtualstore/store")->load((int)$storeid);
        return $store->getGroupId();
    }


    public function getStoresIdsByGroupId($group_id){

        $collection = Mage::getModel('allure_virtualstore/store')->getCollection();

        $collection->addFieldToFilter('group_id', (int)$group_id);

        $stores=array();

        foreach ($collection as $st)
        {

            $stores[]=$st->getId();

        }

        return implode(",",$stores);
    }


}
