<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Ecp_Celebrities_Model_Mysql4_Outfits_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_celebrities/outfits');
    }
}