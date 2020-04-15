<?php
/**
 * 
 * @author allure
 *
 */
class Ecp_ReportToEmail_Model_Resource_Reportlog extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ecp_reporttoemail/reportlog', 'id');
    }
}

