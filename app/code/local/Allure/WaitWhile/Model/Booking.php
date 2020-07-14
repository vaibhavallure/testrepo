<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Model_Booking extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_waitwhile/booking');
    }
}

