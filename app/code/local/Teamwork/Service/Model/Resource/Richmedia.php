<?php

class Teamwork_Service_Model_Resource_Richmedia extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_service/service_setting_rich_content', 'entity_id');
    }
}