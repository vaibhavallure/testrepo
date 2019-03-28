<?php

class Teamwork_Service_Model_Dam_Style extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/dam_style');
    }

    public function addCHQStyleData($flag = null)
    {
        return $this->getResource()->addCHQStyleData($flag);
    }

    public function addCHQItemsData($flag = null)
    {
        return $this->getResource()->addCHQItemsData($flag);
    }

}
