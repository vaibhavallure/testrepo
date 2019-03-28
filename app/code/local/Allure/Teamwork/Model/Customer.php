<?php

class Allure_Teamwork_Model_Customer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_teamwork/customer');
    }
    
}
