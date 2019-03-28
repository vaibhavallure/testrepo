<?php

class Allure_Teamwork_Model_Resource_Duplcustomer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_teamwork/duplcustomer');
    }

}
