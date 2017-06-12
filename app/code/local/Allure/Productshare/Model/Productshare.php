<?php

class Allure_Productshare_Model_Productshare extends Mage_Core_Model_Abstract
{

    protected function _construct ()
    {
        parent::_construct();
        $this->_init('productshare/productshare');
    }
}