<?php

class Allure_Inventory_Model_Draftitems extends Mage_Core_Model_Abstract
{
	protected  function _construct()
	{
		parent::_construct();
		$this->_init('inventory/draftitems');
	}
	public function getItemByProductIdAndOrderId(){
		
	}
}