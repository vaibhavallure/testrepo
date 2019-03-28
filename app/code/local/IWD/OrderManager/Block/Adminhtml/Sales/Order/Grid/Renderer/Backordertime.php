<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Backordertime extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Abstract
{
    protected function loadItems11()
    {
        $order_id = $this->getOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $order_item_collection = $order->getAllVisibleItems();
        $items = array();
        $storeId=$order->getStoreId();
        foreach($order_item_collection as $item){
        	//Allure comment the code
           	/* $stockStatus = Mage::getModel('catalog/product')->load($item->getProductId())->getCustomStockStatus();
            //403 stockStatus is backorder-ship-time
            if($stockStatus == 403 && $item->getBackorderTime() != null) {   */    
        	
            /* if($item->getBackorderTime() != null) {  //Allure new code
            	$items[] = $item->getBackorderTime();
            } else {
                $items[] = '&nbsp;';
            }
             */
            
          /*   $product=Mage::getModel("catalog/product")
            ->setStoreId($storeId)->load(); */
            $product = Mage::getModel('catalog/product');
            $product->setStoreId($storeId)->load($product->getIdBySku($item->getSku()));
            if(!empty($product)){
                $stock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$storeId);
                Mage::log($product->getId()."=".$stock->getQty(),Zend_log::DEBUG,'mylogs',true);
                if(($stock->getQty()>=1 && $stock->getIsInStock())||($product->getStockItem()->getManageStock()==0)){
                    $items[] = '&nbsp;';
                }
                else{
                    if($product->getBackorderTime())
                        $items[] = $product->getBackorderTime();
                    else 
                        $items[] ='Backordered';
                }
            }else {
                if($item->getBackorderTime() != null) {  //Allure new code
                    $items[] = $item->getBackorderTime();
                } else {
                    $items[] = '&nbsp;';
                }
            }
        }

        return $items;
    }
    
    protected function loadItems()
    {
        $order_id = $this->getOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $order_item_collection = $order->getAllVisibleItems();
        $items = array();
        $storeId=$order->getStoreId();
        foreach($order_item_collection as $item){
            if($item->getBackorderTime() != null) {  //Allure new code
                $items[] = $item->getBackorderTime();
            } else {
                 $items[] = '&nbsp;';
            }
        }
        return $items;
    }

    protected function Grid()
    {
        $items = $this->loadItems();
        return $this->formatBigData($items);
    }

    protected function Export()
    {
        $items = $this->loadItems();
        return implode(',', $items);
    }
}
