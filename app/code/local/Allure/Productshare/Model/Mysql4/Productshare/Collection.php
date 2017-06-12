<?php

class Allure_Productshare_Model_Mysql4_Productshare_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct ()
    {
        parent::_construct();
        $this->_init('productshare/productshare');
    }
}