<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     *
     */
    public function __construct()
    {
        switch (Mage::registry('store_type')) {
            case 'website':
                $this->_objectId = 'website_id';
                $saveLabel   = Mage::helper('allure_virtualstore')->__('Save Website');
                $deleteLabel = Mage::helper('allure_virtualstore')->__('Delete Website');
                $deleteUrl   = $this->getUrl('*/*/deleteWebsite', array('item_id' => Mage::registry('store_data')->getId()));
                break;
            case 'group':
                $this->_objectId = 'group_id';
                $saveLabel   = Mage::helper('allure_virtualstore')->__('Save Store');
                $deleteLabel = Mage::helper('allure_virtualstore')->__('Delete Store');
                $deleteUrl   = $this->getUrl('*/*/deleteGroup', array('item_id' => Mage::registry('store_data')->getId()));
                break;
            case 'store':
                $this->_objectId = 'store_id';
                $saveLabel   = Mage::helper('allure_virtualstore')->__('Save Store View');
                $deleteLabel = Mage::helper('allure_virtualstore')->__('Delete Store View');
                $deleteUrl   = $this->getUrl('*/*/deleteStore', array('item_id' => Mage::registry('store_data')->getId()));
                break;
        }
        $this->_blockGroup = 'allure_virtualstore';
        $this->_controller = 'adminhtml_system_store';

        parent::__construct();

        $this->_updateButton('save', 'label', $saveLabel);
        $this->_updateButton('delete', 'label', $deleteLabel);
        $this->_updateButton('delete', 'onclick', 'setLocation(\''.$deleteUrl.'\');');

        //if (!Mage::registry('store_data')->isCanDelete()) {
            $this->_removeButton('delete');
        //}
        if (Mage::registry('store_data')->isReadOnly()) {
            $this->_removeButton('save')->_removeButton('reset');
        }
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        switch (Mage::registry('store_type')) {
            case 'website':
                $editLabel = Mage::helper('allure_virtualstore')->__('Edit Website');
                $addLabel  = Mage::helper('allure_virtualstore')->__('New Website');
                break;
            case 'group':
                $editLabel = Mage::helper('allure_virtualstore')->__('Edit Store');
                $addLabel  = Mage::helper('allure_virtualstore')->__('New Store');
                break;
            case 'store':
                $editLabel = Mage::helper('allure_virtualstore')->__('Edit Store View');
                $addLabel  = Mage::helper('allure_virtualstore')->__('New Store View');
                break;
        }

        return Mage::registry('store_action') == 'add' ? $addLabel : $editLabel;
    }
}
