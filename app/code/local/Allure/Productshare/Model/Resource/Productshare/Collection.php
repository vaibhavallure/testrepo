<?php

class Allure_Productshare_Model_Resource_Productshare_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
    	parent::_construct();
        $this->_init('productshare/productshare');
    }
}