<?php

class Ecp_Footlinks_Model_Mysql4_Footlinks extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the slides_id refers to the key field in your database table.
        $this->_init('ecp_footlinks/footlinks', 'footlink_id');
    }
}