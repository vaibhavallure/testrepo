<?php

class Allure_Appointments_Model_Timing extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('appointments/timing');
    }

    
}