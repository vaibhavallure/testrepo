<?php
class OpsWay_CustomOrderManager_Model_Observer {
	
	public function setBackorderTimeAttribute(Varien_Event_Observer $observer) {

	    $item = $observer->getQuoteItem();
	    $product = $observer->getProduct();
	   	
	   	//$item->setBackorderTime($product->getBackorderTime()); //Comment by Allure
	    
	    //Allure Inc, Modification Date:21/06/2017 
	    ////START
	    $productId = Mage::getSingleton('catalog/product')->getIdBySku($item->getSku());
	    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	    $stockQty = intval($stockItem->getQty());
	    if ($stockQty <= 0) {
	    	$item->setBackorderTime($product->getBackorderTime());
	    }
	    ////END
	    
	    return $this;
	}
}