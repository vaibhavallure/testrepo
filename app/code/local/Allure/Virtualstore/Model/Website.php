<?php

class Allure_Virtualstore_Model_Website extends Mage_Core_Model_Abstract
{
    private $_isReadOnly = false;
    protected $_isCanDelete;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/website');
    }
    public function getWebsiteIds()
    {
        $collection = Mage::getModel('allure_virtualstore/website')->getCollection();
        $wbStrore=$collection->getData();
        $websiteArray = array();
        foreach ($wbStrore as $c)
        {
            $websiteArray[] = array('label'=>$c['name'],'value'=>$c['website_id']);
        }
        return $websiteArray;
    }
    public function getWebsite()
    {
        $collection = Mage::getModel('allure_virtualstore/website')->getCollection();
        $websiteArray = array();
        foreach ($collection as $c)
        {
            $websiteArray[$c->getData('website_id')] = $c->getData('name');
        }
        return $websiteArray;
    }
    
    public function getGroupCollection()
    {
        return Mage::getModel('allure_virtualstore/group')
        ->getCollection()
        ->addFieldToFilter("website_id",$this->getId());
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
     * is can delete website
     *
     * @return bool
     */
    public function isCanDelete()
    {
        if ($this->_isReadOnly || !$this->getId()) {
            return false;
        }
        if (is_null($this->_isCanDelete)) {
            $this->_isCanDelete = (Mage::getModel('allure_virtualstore/website')->getCollection()->getSize() > 2)
            && !$this->getIsDefault();
        }
        return $this->_isCanDelete;
    }
}
