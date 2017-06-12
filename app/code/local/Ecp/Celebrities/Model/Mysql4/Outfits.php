<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Ecp_Celebrities_Model_Mysql4_Outfits extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the celebrity_id refers to the key field in your database table.
        $this->_init('ecp_celebrities/outfits', 'celebrity_outfit_id');
    }
}
