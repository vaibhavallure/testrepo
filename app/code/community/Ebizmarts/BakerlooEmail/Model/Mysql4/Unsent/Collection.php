<?php
class Ebizmarts_BakerlooEmail_Model_Mysql4_Unsent_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('bakerloo_email/unsent');
    }
}
