<?php

class Teamwork_Common_Model_Staging_Resource_Brand extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service_brand', 'entity_id');
    }
}