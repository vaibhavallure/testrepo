<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Backordertime extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Abstract
{
    protected function loadItems()
    {
        $order_id = $this->getOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $order_item_collection = $order->getAllVisibleItems();
        $items = array();

        foreach($order_item_collection as $item){
        	//Allure comment the code
           	/* $stockStatus = Mage::getModel('catalog/product')->load($item->getProductId())->getCustomStockStatus();
            //403 stockStatus is backorder-ship-time
            if($stockStatus == 403 && $item->getBackorderTime() != null) {   */    
        	
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
