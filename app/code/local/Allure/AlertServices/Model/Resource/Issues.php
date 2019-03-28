<?php

class Allure_AlertServices_Model_Resource_Issues extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('alertservices/issues', 'id');
    }
    
}
