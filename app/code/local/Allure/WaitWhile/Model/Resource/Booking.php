<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Model_Resource_Booking extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_waitwhile/booking', 'booking_id');
    }
    
}
