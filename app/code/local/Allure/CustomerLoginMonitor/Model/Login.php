<?php

/**
* Created by allure.
* User: indrajitpatil
* Date: 28/03/19
* Time: 15:13 PM
*/

class Allure_CustomerLoginMonitor_Model_Login extends Mage_Core_Model_Abstract{
    protected function _construct()
    {
        $this->_init('customerloginmonitor/login');
    }
    protected function _beforeSave() {
        parent::_beforeSave();
        $currentTime = Varien_Date::now();
        $this->setDate($currentTime);
        return $this;
    }
}