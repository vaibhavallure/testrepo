<?php

class Teamwork_Common_Model_Staging_Resource_Service_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/staging_service');
    }
}
