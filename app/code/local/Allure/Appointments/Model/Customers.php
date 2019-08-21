<?php

class Allure_Appointments_Model_Customers extends Mage_Core_Model_Abstract
{

    protected function _construct ()
    {
        parent::_construct();
        $this->_init('appointments/customers');
    }
}