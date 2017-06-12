<?php

class Allure_Inventory_Model_Lowstock extends Mage_Core_Model_Abstract
{
    protected  function _construct()
    {
    	parent::_construct();
        $this->_init('inventory/lowstock');
    }
}