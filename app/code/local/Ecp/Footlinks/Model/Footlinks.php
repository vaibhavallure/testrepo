<?php

class Ecp_Footlinks_Model_Footlinks extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_footlinks/footlinks');
    }
}