<?php

class Teamwork_Service_Model_Resource_Chqmappingfields_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/chqmappingfields');
    }
}