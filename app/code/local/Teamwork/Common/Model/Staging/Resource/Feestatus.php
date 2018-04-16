<?php

class Teamwork_Common_Model_Staging_Resource_Feestatus extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service_fee_status', 'entity_id');
    }
}