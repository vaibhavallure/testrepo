<?php

class Allure_Londoninventory_Model_Inventory extends Mage_Core_Model_Abstract
{
    protected  function _construct()
    {
    	parent::_construct();
        $this->_init('allure_londoninventory/inventory');
    }
}