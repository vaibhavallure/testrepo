<?php

class Teamwork_Service_Model_Resource_Mappingproperty extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_service/service_setting_attribute_mapping', 'entity_id');
    }
}