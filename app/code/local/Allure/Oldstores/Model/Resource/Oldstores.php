<?php

class Allure_Oldstores_Model_Resource_Oldstores extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_oldstores/oldstores', 'id');
    }
    
}
