<?php

class Allure_Oldstores_Model_Resource_Oldstores_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_oldstores/oldstores');
    }

}
