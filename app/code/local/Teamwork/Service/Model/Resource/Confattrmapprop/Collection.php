<?php

class Teamwork_Service_Model_Resource_Confattrmapprop_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/confattrmapprop');
        foreach($this->getResource()->getFieldsAliases() as $alias => $field)
        {
            $this->addFilterToMap($alias, $field);
        }
    }

    public function _initSelect()
    {
        $this->_select = $this->getResource()->getRawSelect();
        return $this;
    }

}
