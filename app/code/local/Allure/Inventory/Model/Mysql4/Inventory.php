<?php

class Allure_Inventory_Model_Mysql4_Inventory extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
    	parent::_construct();
        $this->_init('inventory/inventory', 'id');
    }
}