<?php

class Allure_Inventory_Model_Resource_Transfer extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('inventory/transfer', 'id');
	}
}