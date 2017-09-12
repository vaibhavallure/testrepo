<?php

class Allure_MyAccount_Helper_Data extends Mage_Customer_Helper_Data
{
	const STORE_COLOR_MAPPING_XML = "myaccount/general/storemapping";
	const ORDER_STATE_MAPPING_XML = "myaccount/general/order_message_mapping";
	
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
    public function getProductCurrentStatus($sku,$qty){
        $orderType  = Mage::app()->getRequest()->getParam('order_type');
        $stockMsg = "";
        $isShow = false;
        if($orderType=="open"){
            $productId  = Mage::getModel('catalog/product')->getIdBySku($sku);
            $product    = Mage::getModel('catalog/product')->load($productId);
            $stockItem  = Mage::getModel('cataloginventory/stock_item')
                            ->loadByProduct($product);
            $stockQty   = intval($stockItem->getQty());
            if($stockQty > 0){
                $stockMsg = "(In Stock: Ships Within 24 hours (Mon-Fri).)";
            }else{ 
                $backTime = $product->getData('backorder_time');
                $stockMsg = "";
                if(!is_null($backTime))
                    $stockMsg = "(The metal color or length combination you selected is backordered. Order now and It will ship "." - ".$backTime.")";
                else 
                    $stockMsg = "(This product is not available in the requested quantity.".$qty." of the items will be backordered.)";
            }
            $isShow = true;
        }
        return array("is_show"=>$isShow,"message"=>$stockMsg);
    }
        
}
