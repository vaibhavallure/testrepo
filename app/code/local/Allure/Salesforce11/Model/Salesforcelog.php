<?php
/**
 * 
 * @author aws02
 *
 */
class Allure_Salesforce_Model_Salesforcelog extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_salesforce/salesforcelog');
    }
}
