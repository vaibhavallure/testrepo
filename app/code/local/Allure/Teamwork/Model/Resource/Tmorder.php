<?php

class Allure_Teamwork_Model_Resource_Tmorder extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_teamwork/tmorder', 'entity_id');
    }
    
}
