<?php

class Allure_MyAccount_Block_Filter extends Mage_Core_Block_Template{
    const ALL_STORE         = "all";
    const DESC_ORDER        = "desc";
    const OPEN_ORDER_STATUS = "all";
    const ALL_ORDER_STATUS  = "all";
    
    /**
     * get current filter store
     */
    public function getCurrentFilterStore(){
        return (!empty($_GET['m_store'])) ? $_GET['m_store'] : self::ALL_STORE;
    }
    
    /**
     * get current filter sort order by date
     */
    public function getCurrentFilterSortOrder(){
        return (!empty($_GET['m_sort'])) ? $_GET['m_sort'] : self::DESC_ORDER;
    }
    
    /**
     * get current filter of open order status
     */
    public function getCurrentFilterOpenOrderStatus(){
        return (!empty($_GET['m_order']) ? $_GET['m_order'] : self::OPEN_ORDER_STATUS);
    }
    
    /**
     * get current filter of all order status
     */
    public function getCurrentFilterAllOrderStatus(){
        return (!empty($_GET['m_aorder']) ? $_GET['m_aorder'] : self::ALL_ORDER_STATUS);
    }
    
    /**
     * get all stores
     */
    public function getAllStores(){
        if (Mage::helper('myaccount')->isVirtualStoreActive()){
            return Mage::helper("allure_virtualstore")->getVirtualStores();
        }
        return Mage::app()->getStores();
        
    }
    
    /**
     * get sort order 
     */
    public function getSortOrderArray(){
        return array('desc'=>'Latest Purchase','asc'=>'Oldest Purchase');
    }
    
    /**
     * get store array list by id & name
     */
    public function getStoreListArray(){
        $storeArr = array();
        $stores   = $this->getAllStores();
        foreach ($stores as $store) {
            $storeArr[$store->getId()] = $store->getName();
        }
        return $storeArr;
    }
    
    /**
     * get open order status array
     */
    public function getOrderFilterStatusArray($orderType = 0){
        $helper             = Mage::helper("myaccount");
        $status             = Mage::getSingleton('sales/order_config')
                                ->getStatuses();
        $status_state       = $helper->getOrderMessages();
        if(!count($status_state) > 0){
            $status_state   = Mage::getSingleton('sales/order_config')
                                ->getStates();
        }
        
        foreach ($status as $key=>$value){
            if(!array_key_exists($key, $status_state)){
                $status_state[$key] = $value;
            }
        }
        
        $openStatusArr      = array('holded','pending','processing');
        $closeStatusArr     = array('canceled','complete','closed');
        $orderStatusArr     = array("all"=>"All Status");
        if($orderType == $helper::OPEN_ORDER){
            foreach ($status_state as $key => $value){
                if(!in_array($key, $closeStatusArr)){
                    if(array_key_exists($key, $status_state))
                        $orderStatusArr[$key] = $status_state[$key];
                    else 
                        $orderStatusArr[$key] = $status[$key];
                }
                //$orderStatusArr[$key] = $status[$key];
            }
        }else{
            foreach ($closeStatusArr as $key){
                if(array_key_exists($key, $status_state))
                    $orderStatusArr[$key] = $status_state[$key];
                else 
                    $orderStatusArr[$key] = $status[$key];
            }
        }
        return $orderStatusArr;
    }
}
