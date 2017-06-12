<?php

class Ecp_Familycolors_Model_Mysql4_Familycolors_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_familycolors/familycolors');
    }
	   
}