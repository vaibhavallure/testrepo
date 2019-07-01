<?php
/**
 * Created by allure.
 * User: indrajitpatil
 * Date: 28/03/19
 * Time: 15:30 PM
 */

class Allure_CustomerLoginMonitor_Model_Resource_Login_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerloginmonitor/login');
    }
}