<?php

class Teamwork_Common_Model_Staging_Resource_Discount extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service_discount', 'entity_id');
    }
}