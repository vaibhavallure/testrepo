<?php

class Allure_AlertServices_Model_Resource_Issues_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('alertservices/issues');
    }

}
