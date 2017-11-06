<?php

class Allure_Londoninventory_Model_Resource_Inventory extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_londoninventory/inventory', 'id');
    }
}