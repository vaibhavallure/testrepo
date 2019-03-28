<?php

class Teamwork_Service_Model_Resource_Chqmappingfields extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_service/service_chq_mappingfields', 'entity_id');
    }
}