<?php

class Allure_Productshare_Model_Resource_Productshare extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct ()
    {
        $this->_init('productshare/productshare', 'ps_id');
    }
}