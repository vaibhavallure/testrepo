<?php

abstract class Teamwork_Common_Model_Staging_Resource_Abstractchanneled extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service_abstractchanneled', 'entity_id');
    }
}