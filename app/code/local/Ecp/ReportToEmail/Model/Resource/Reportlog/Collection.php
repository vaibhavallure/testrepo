<?php
/**
 * 
 * @author allure
 *
 */
class Ecp_ReportToEmail_Model_Resource_Reportlog_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ecp_reporttoemail/reportlog');
    }

}
