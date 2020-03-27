<?php
/**
 * 
 * @author allure
 *
 */
class Ecp_ReportToEmail_Model_Reportlog extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_reporttoemail/reportlog');
    }
}

