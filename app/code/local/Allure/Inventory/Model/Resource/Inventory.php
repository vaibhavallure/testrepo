<?php

class Allure_Inventory_Model_Resource_Inventory extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('inventory/inventory', 'id');
    }
}