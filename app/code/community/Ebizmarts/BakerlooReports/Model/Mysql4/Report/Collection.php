<?php

class Ebizmarts_BakerlooReports_Model_Mysql4_Report_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('bakerloo_reports/report');
    }
}
