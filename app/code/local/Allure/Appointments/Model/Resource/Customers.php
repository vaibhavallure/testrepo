<?php

class Allure_Appointments_Model_Resource_Customers extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct ()
    {
        $this->_init('appointments/customers', 'id');
    }
}