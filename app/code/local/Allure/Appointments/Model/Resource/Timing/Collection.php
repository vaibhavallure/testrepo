<?php

class Allure_Appointments_Model_Resource_Timing_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('appointments/timing');
    }

    
}