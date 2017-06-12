<?php

class Ecp_Footlinks_Model_Mysql4_Footlinks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_footlinks/footlinks');
    }
}