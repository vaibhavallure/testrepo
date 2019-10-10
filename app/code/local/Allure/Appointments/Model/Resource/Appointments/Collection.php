<?php

class Allure_Appointments_Model_Resource_Appointments_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('appointments/appointments');
    }
/*OVERRIDED GET SIZE METHOD FOR COUNT OF PIERCING & CHECKUP
REFRENCE : https://magento.stackexchange.com/questions/5481/issue-using-having-in-magento-collection
*/
    public function getSize() {

        if (is_null($this->_totalRecords)) {
//            $sql = $this->getSelectCountSql(); FOR CUSTOM FILTER COMMENTED THIS CODE
            $sql = $this->getSelect();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }

    
}