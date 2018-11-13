<?php

class Allure_Virtualstore_Model_Store extends Mage_Core_Model_Abstract
{
    private $_isReadOnly = false;
    
    protected $_group;
    
    protected function _construct()
    {
        $this->_init("allure_virtualstore/store");
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
            $this->_isReadOnly = (bool) $value;
        }
        return $this->_isReadOnly;
    }
    
    /**
     * Check if store can be deleted
     *
     * @return boolean
     */
    public function isCanDelete()
    {
        if (!$this->getId()) {
            return false;
        }
        
        return $this->getGroup()->getDefaultStoreId() != $this->getId();
    }
    
    /**
     * Retrieve group model
     *
     * @return Mage_Core_Model_Store_Group
     */
    public function getGroup()
    {
        if (is_null($this->getGroupId())) {
            return false;
        }
        if (is_null($this->_group)) {
            $this->_group = Mage::getModel('allure_virtualstore/group')->load($this->getGroupId());
        }
        return $this->_group;
    }
    
}
