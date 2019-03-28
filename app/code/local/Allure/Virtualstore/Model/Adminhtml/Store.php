<?php

class Allure_Virtualstore_Model_Adminhtml_Store extends Varien_Object
{
    protected $_websiteCollection   = array();
    protected $_groupCollection     = array();
    protected $_storeCollection     = array();
    protected $_virtualStoreHelper  = null;
    
    private function initiaze(){
        if ($this->_virtualStoreHelper == null){
            $this->_virtualStoreHelper = Mage::helper("allure_virtualstore");
        }
        return $this->_virtualStoreHelper;
    }
    
    public function __construct(){
        $this->initiaze();
        return $this->reload();
    }
    
    public function reload(){
        $this->_loadWebsiteCollection();
        $this->_loadGroupCollection();
        $this->_loadStoreCollection();
        return $this;
    }
    
    public function getWebsiteCollection(){
        return $this->_loadWebsiteCollection();
    }
    
    public function getGroupCollection(){
        return $this->_loadGroupCollection();
    }
    
    public function getStoreCollection(){
        return $this->_loadStoreCollection();
    }
    
    /**
     * load virtual website collection
     */
    protected function _loadWebsiteCollection(){
        $this->_websiteCollection = $this->_virtualStoreHelper->getVirtualWebsites();
        return $this;
    }
    
    /**
     * load virtual store group collection
     */
    protected function _loadGroupCollection(){
        $this->_groupCollection = $this->_virtualStoreHelper->getVirtualGroups();
        return $this;
    }
    
    /**
     * load virtual store collection
     */
    protected function _loadStoreCollection(){
        
        $this->_storeCollection = $this->_virtualStoreHelper->getVirtualStores();
        return $this;
    }
    
    /**
     * Retrieve store values for form
     *
     * @param bool $empty
     * @param bool $all
     * @return array
     */
    public function getStoreValuesForForm($empty = false, $all = false)
    {
        $options = array();
        if ($empty) {
            $options[] = array(
                'label' => '',
                'value' => ''
            );
        }
        if ($all ) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('All Store Views'),
                'value' => 0
            );
        }
        
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        
        foreach ($this->_websiteCollection as $website) {
            $websiteShow = false;
            foreach ($this->_groupCollection as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($this->_storeCollection as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $options[] = array(
                            'label' => $website->getName(),
                            'value' => array()
                        );
                        $websiteShow = true;
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $values    = array();
                    }
                    $values[] = array(
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $store->getName(),
                        'value' => $store->getId()
                    );
                }
                if ($groupShow) {
                    $options[] = array(
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $group->getName(),
                        'value' => $values
                    );
                }
            }
        }
        return $options;
    }
    
    
    /**
     * get store key value pair
     */
    public function getStoreOptionHash(){
        $stores = $this->_virtualStoreHelper->getVirtualStores();
        $options = array();
        foreach ($stores as $store){
            $options[$store->getId()] = $store->getName();
        }
        return $options;
    }
    
    
}
