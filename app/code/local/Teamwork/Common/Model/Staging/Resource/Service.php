<?php
class Teamwork_Common_Model_Staging_Resource_Service extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service', 'entity_id');
    }
}