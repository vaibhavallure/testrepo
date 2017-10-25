<?php

class Allure_MyAccount_Block_Purchase extends Mage_Core_Block_Template
{
	public function __construct(){
	    $helper     = Mage::helper("myaccount");
	    $collection = $helper->getPurchaseItems();
		$this->setPurchaseOrderCollection($collection);
	}
	
	protected function _prepareLayout(){
		parent::_prepareLayout();
		$headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock) {
			$headBlock->setTitle($this->__('My Products'));
		}
	}
}
