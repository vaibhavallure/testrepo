<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Pphtoken extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('bakerloo_restful/pphtoken', 'token_id');
    }
}
