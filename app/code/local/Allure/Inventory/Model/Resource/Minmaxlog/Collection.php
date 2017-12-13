<?php

class Allure_Inventory_Model_Resource_Minmaxlog_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
    	parent::_construct();
        $this->_init('inventory/minmaxlog');
    }
}