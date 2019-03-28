<?php

class Allure_Inventory_Model_Resource_Vendorwork extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('inventory/vendorwork', 'id');
	}
}