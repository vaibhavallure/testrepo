<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Model_Resource_Services_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_waitwhile/services');
    }

}
