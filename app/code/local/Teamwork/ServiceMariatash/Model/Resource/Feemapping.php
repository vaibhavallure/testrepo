<?php

class Teamwork_ServiceMariatash_Model_Resource_Feemapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_servicemariatash/service_fee_mapping', 'entity_id');
    }
}