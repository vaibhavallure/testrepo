<?php

class Allure_Virtualstore_Model_Group extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/group');
    }
    public function getGroupIds()
    {
        $collection = Mage::getModel('allure_virtualstore/group')->getCollection();
        $gpStrore=$collection->getData();
        foreach ($gpStrore as $c)
        {
            $groupArray[] = array('label'=>$c['name'],'value'=>$c['group_id']);
        }
        return $groupArray;
    }
}
