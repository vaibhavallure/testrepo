<?php

class Ecp_Familycolors_Model_Mysql4_Familycolors extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('ecp_familycolors/familycolors', 'colorfamily_id');
    }
}