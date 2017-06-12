<?php
class OpsWay_CustomOrderManager_Model_Observer {
	
	public function setBackorderTimeAttribute(Varien_Event_Observer $observer) {

	    $item = $observer->getQuoteItem();
	    $product = $observer->getProduct();
	    $item->setBackorderTime($product->getBackorderTime());
	    return $this;
	}
}