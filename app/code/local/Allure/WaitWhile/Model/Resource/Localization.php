<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Model_Resource_Localization extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_waitwhile/localization', 'locale_id');
    }
}

