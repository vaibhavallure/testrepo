<?php

class Allure_Virtualstore_Model_Website extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/website');
    }
    
}
