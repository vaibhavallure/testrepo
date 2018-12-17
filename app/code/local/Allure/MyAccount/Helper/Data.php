<?php

class Allure_MyAccount_Helper_Data extends Mage_Customer_Helper_Data
{
	const STORE_COLOR_MAPPING_XML = "myaccount/general/storemapping";
	const ORDER_STATE_MAPPING_XML = "myaccount/general/order_message_mapping";
	
	//purchase item settings
	const PURCHASE_ITEM_SKU_ESCAPE_XML = "myaccount/purchase_item_settings/purchase_item_escape_sku";
	
	//order status flag
	const OPEN_ORDER = 1;
	const ALL_ORDER  = 2;
	
	const MAIN_STORE_ID = 1;
	
    public function getStoreColorConfig(){
    	$storeColorConfig = Mage::getStoreConfig(self::STORE_COLOR_MAPPING_XML);
    	$config=unserialize($storeColorConfig);
    	$storeConf = array();
    	foreach ($config as $conf){
    		$storeConf[$conf['store']] = $conf;
    	}
    	return $storeConf;
    }
    
    public function getOrderMessages(){
        $storeColorConfig = Mage::getStoreConfig(self::ORDER_STATE_MAPPING_XML);
        $config=unserialize($storeColorConfig);
        $orderLabelArr = array();
        foreach ($config as $conf){
            $orderLabelArr["{$conf['order_state']}"] = $conf['label'];
        }
        return $orderLabelArr;
    }
    
    public function getTrackingPopupUrlBySalesModel($model)
    {
    	if ($model instanceof Mage_Sales_Model_Order) {
    		return $this->_getTrackingUrl('order_id', $model);
    	} elseif ($model instanceof Mage_Sales_Model_Order_Shipment) {
    		return $this->_getTrackingUrl('ship_id', $model);
    	} elseif ($model instanceof Mage_Sales_Model_Order_Shipment_Track) {
    		return $this->_getTrackingUrl('track_id', $model, 'getEntityId');
    	}
    	return '';
    }
    
    /**
     * get sku string separated by comma
     */
    public function getPurchaseEscapeSKU(){
        return Mage::getStoreConfig(self::PURCHASE_ITEM_SKU_ESCAPE_XML);
    }
    
    /**
     * Retrieve tracking ajax url
     *
     * @return string
     */
    public function getTrackingAjaxUrl()
    {
    	return $this->_getUrl('myaccount/index/track');
    }
    
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
    	if (empty($model)) {
    		$param = array($key => ''); // @deprecated after 1.4.0.0-alpha3
    	} else if (!is_object($model)) {
    		$param = array($key => $model); // @deprecated after 1.4.0.0-alpha3
    	} else {
    		$param = array(
    				'hash' => Mage::helper('core')->urlEncode("{$key}:{$model->$method()}:{$model->getProtectCode()}")
    		);
    	}
    	$storeId = is_object($model) ? $model->getStoreId() : null;
    	$storeModel = Mage::app()->getStore($storeId);
    	return $storeModel->getUrl('myaccount/index/track', $param);
    }
    
    /*
     * get product current stock status
     */
    public function getProductCurrentStatus($item,$sku,$qty,$storeId){
        $orderType  = Mage::app()->getRequest()->getParam('order_type');
        $stockMsg = "";
        $isShow = false;
        
        if($this->isVirtualStoreActive()){
            $storeId = ($item->getOldStoreId())?$item->getOldStoreId():$item->getStoreId();
        }else{
            $storeId = $item->getStoreId();
        }
        
        if(empty($storeId)){
            return array("is_show"=>$isShow,"message"=>$stockMsg);
        }
        
        if($storeId == 1){
            if($orderType == "open"){
                $amstockHelper = Mage::helper('amstockstatus');
                $stockMsg = $amstockHelper->getOrderSalesProductStockStatus($item);
                $isShow = true;
            }
        }
        return array("is_show"=>$isShow,"message"=>$stockMsg);
    }
    
    /**
     * get customer's order purchased item collection
     */
    public function getPurchaseItems(){
        $request    = Mage::app()->getRequest()->getParams();
        $pageNo     = 1;
        $limit      = 10;
        $store      = "all";
        $sortOrder  = "desc";
        
        $escapeSKU  = $this->getPurchaseEscapeSKU();
        
        if(count($request)){
            if($request['page']){
                $pageNo=$request['page'];
            }
            if($request['limit']){
                $limit = $request['limit'];
            }
            if(!empty($request['m_store'])){
                $store = $request['m_store'];
            }
            if(!empty($request['m_sort'])){
                $sortOrder = $request['m_sort'];
            }
        }
        
        $collection = Mage::getResourceModel('sales/order_item_collection')
                        ->addAttributeToSelect('*');
        $collection->getSelect()->join( array('orders'=> sales_flat_order),
                'orders.entity_id=main_table.order_id',
                array('orders.customer_email','orders.customer_id')
            );
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $collection->addFieldToFilter('customer_id',$customer->getId());
        $collection->addFieldToFilter('parent_item_id',array('null' => true));
        $collection->addFieldToFilter('orders.state',array('in'=>array('complete','processing')));
        $collection->addFieldToFilter('price',array('gt' => 0));
        
        //escape sku string from purchase item
        if(!empty($escapeSKU) && $escapeSKU != ""){
            $escapeSKU = explode(",", $escapeSKU);
            $collection->addFieldToFilter('sku',array('nin' => $escapeSKU));
        }
        
        if(!empty($store)){
            if($store!='all'){
                if($this->isVirtualStoreActive()){
                   /*  if($store == self::MAIN_STORE_ID){
                        $collection->getSelect()->where("main_table.old_store_id = {$store} OR (main_table.old_store_id = 0 AND main_table.store_id = {$store}) ");
                    }else{
                        $collection->addFieldToFilter('main_table.old_store_id',$store);
                    } */
                    $collection->addFieldToFilter('orders.old_store_id',$store);
                }else{
                    $collection->addFieldToFilter('main_table.store_id',$store);
                }
            }
        }
        if(!empty($sortOrder)){
            $collection->setOrder('main_table.created_at', $sortOrder);
        }
        
        $collection->setCurPage($pageNo);
        $collection->setPageSize($limit);
        return $collection;
    }
    
    /**
     * get all order history collection
     */
    public function getOrdersHistory($orderFlag){
        $request = Mage::app()->getRequest()->getParams();
        $pageNo             = 1;
        $limit              = 10;
        $store              = "all";
        $sortOrder          = "desc";
        $openOrderStatus    = "all";
        $closeOrderStatus   = "all";
        
        if(count($request)>0){
            if(!empty($request['m_store'])){
                $store = $request['m_store'];
            }
            if(!empty($request['m_sort'])){
                $sortOrder = $request['m_sort'];
            }
            if($request['page']){
               $pageNo = $request['page'];
            }
            if($request['limit']){
               $limit = $request['limit'];
            }
            if($request['m_order']){
                $openOrderStatus = $request['m_order'];
            }
            if($request['m_aorder']){
                $closeOrderStatus = $request['m_aorder'];
            }
        }
        $customer   = Mage::getSingleton('customer/session')->getCustomer();
        $collection = Mage::getResourceModel('sales/order_collection')
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', $customer->getId());
        
        if($orderFlag == self::ALL_ORDER){
            $collection->addFieldToFilter('state', array('in' => array('canceled','complete','closed'))); 
            if(!empty($closeOrderStatus)){
                if($closeOrderStatus != "all"){
                    $collection->addFieldToFilter('state',$closeOrderStatus);
                }
            }
        }elseif($orderFlag == self::OPEN_ORDER){
            $collection->addFieldToFilter('state', array('nin' => array('canceled','complete','closed'))); 
            if(!empty($openOrderStatus)){
                if($openOrderStatus != "all"){
                    $collection->addFieldToFilter('state',$openOrderStatus);
                }
            }
        }
        
        if(!empty($store)){
            if($store!='all'){
                if($this->isVirtualStoreActive()){
                   /*  if($store == self::MAIN_STORE_ID){
                        $collection->getSelect()->where("main_table.old_store_id = {$store} OR (main_table.old_store_id = 0 AND main_table.store_id = {$store}) ");
                    }else{
                        $collection->addFieldToFilter('main_table.old_store_id',$store);
                    } */
                    $collection->addFieldToFilter('main_table.old_store_id',$store);
                }else{
                    $collection->addFieldToFilter('main_table.store_id',$store);
                }
            }
        }
                
        $collection->setOrder('main_table.created_at', $sortOrder);
        $collection->setCurPage($pageNo);
        $collection->setPageSize($limit);
        return $collection;
    }
    
    /**
     * return true | false
     */
    public function isVirtualStoreActive(){
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore'))
            return true;
        return false;
    }
        
}
