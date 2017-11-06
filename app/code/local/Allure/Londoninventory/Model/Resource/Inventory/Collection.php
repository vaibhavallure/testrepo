<?php

class Allure_Londoninventory_Model_Resource_Inventory_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
    	parent::_construct();
        $this->_init('allure_londoninventory/inventory');
    }
}