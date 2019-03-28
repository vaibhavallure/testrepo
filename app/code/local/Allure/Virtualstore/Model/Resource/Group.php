<?php

class Allure_Virtualstore_Model_Resource_Group extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_virtualstore/group', 'group_id');
    }
    
}
