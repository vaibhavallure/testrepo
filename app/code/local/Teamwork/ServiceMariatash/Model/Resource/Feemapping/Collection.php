<?php

class Teamwork_ServiceMariatash_Model_Resource_Feemapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_servicemariatash/feemapping');
    }
}