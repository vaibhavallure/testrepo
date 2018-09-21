<?php

class Allure_AlertServices_Model_Issues extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('alertservices/issues');
    }
    
}
