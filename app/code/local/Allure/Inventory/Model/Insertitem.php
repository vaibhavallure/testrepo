<?php

class Allure_Inventory_Model_Insertitem extends Mage_Core_Model_Abstract
{
	protected  function _construct()
	{
		parent::_construct();
		$this->_init('inventory/insertitem');
	}
	
}