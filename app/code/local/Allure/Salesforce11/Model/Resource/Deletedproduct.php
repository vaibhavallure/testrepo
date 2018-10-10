<?php
/**
 * 
 * @author aws02
 *
 */
class Allure_Salesforce_Model_Resource_Deletedproduct extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_salesforce/deletedproduct','id');
    }
}
