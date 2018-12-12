<?php
class OpsWay_CustomOrderManager_Model_Observer {
	
	public function setBackorderTimeAttribute(Varien_Event_Observer $observer) {

	    $item = $observer->getQuoteItem();
	    $product = $observer->getProduct();

	   	//$item->setBackorderTime($product->getBackorderTime()); //Comment by Allure
	    
	    //Allure Inc, Modification Date:21/06/2017 //AWS12 Date : 15/06/2018
	    ////START
	    $productId = Mage::getSingleton('catalog/product')->getIdBySku($item->getSku());
	    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	    $stockQty = intval($stockItem->getQty());
	    $isGiftCard= Mage::helper('amstockstatus')->isGiftcardProduct($product->getSku());
	    Mage::log('is gift card : '.$isGiftCard,Zend_log::DEBUG,'gift-card.log',true);

        if (($stockQty <= 0 || $stockQty<$item->getQty()) && !$isGiftCard) {
	    	if($product->getBackorderTime()){
	    		$item->setBackorderTime($product->getBackorderTime());
	    	}else{
	    		$item->setBackorderTime("backorder");
	    	}
	    	
	    }
	    ////END
	    
	    return $this;
	}
}