<?php
class Allure_CustomerLoginMonitor_Model_Resource_Login extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('customerloginmonitor/login','row_id');
    }

}