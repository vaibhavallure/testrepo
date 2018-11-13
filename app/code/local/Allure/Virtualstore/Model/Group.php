<?php

class Allure_Virtualstore_Model_Group extends Mage_Core_Model_Abstract
{
    private $_isReadOnly = false;
    
    protected $_website;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/group');
    }
    public function getGroupIds()
    {
        $collection = Mage::getModel('allure_virtualstore/group')->getCollection();
        $gpStrore=$collection->getData();
        $groupArray = array();
        foreach ($gpStrore as $c)
        {
            $groupArray[] = array('label'=>$c['name'],'value'=>$c['group_id']);
        }
        return $groupArray;
    }
    public function getGroup()
    {
        $collection = Mage::getModel('allure_virtualstore/group')->getCollection();
        $groupArray = array();
        foreach ($collection as $c)
        {
            $groupArray[$c->getData('group_id')] = $c->getData('name');
        }
        return $groupArray;
    }
    
    public function getStoreCollection()
    {
        return Mage::getModel('allure_virtualstore/store')
        ->getCollection()
        ->addFieldToFilter("group_id",$this->getId());
    }
    
    /**
     * Get/Set isReadOnly flag
     *
     * @param bool $value
     * @return bool
     */
    public function isReadOnly($value = null)
    {
        if (null !== $value) {
            $this->_isReadOnly = (bool)$value;
        }
        return $this->_isReadOnly;
    }
    
    /**
     * Is can delete group
     *
     * @return bool
     */
    public function isCanDelete()
    {
        if (!$this->getId()) {
            return false;
        }
        
        return $this->getWebsite()->getDefaultGroupId() != $this->getId();
    }
    
    /**
     * Retrieve website model
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if (is_null($this->getWebsiteId())) {
            return false;
        }
        if (is_null($this->_website)) {
            $this->_website = Mage::getModel('allure_virtualstore/website')->load($this->getWebsiteId());
        }
        return $this->_website;
    }
    
    
}
