<?php
/**
 * 
 * @author aws02
 *
 */
class Allure_Salesforce_Model_Resource_Salesforcelog_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_salesforce/salesforcelog');
    }

}
