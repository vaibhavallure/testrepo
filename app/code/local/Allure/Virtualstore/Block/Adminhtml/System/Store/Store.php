<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Store extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'allure_virtualstore';
        $this->_controller = 'adminhtml_system_store';
        //$this->_controller  = 'system_store';
        $this->_headerText  = Mage::helper('adminhtml')->__('Manage Virtual Stores');
        $this->setTemplate('system/store/container.phtml');
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        /* Add website button */
        $this->_addButton('add', array(
            'label'     => Mage::helper('allure_virtualstore')->__('Create Website'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/newWebsite') .'\')',
            'class'     => 'add',
        ));

        /* Add Store Group button */
        $this->_addButton('add_group', array(
            'label'     => Mage::helper('allure_virtualstore')->__('Create Store'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/newGroup') .'\')',
            'class'     => 'add',
        ));

        /* Add Store button */
        $this->_addButton('add_store', array(
            'label'     => Mage::helper('allure_virtualstore')->__('Create Store View'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/newStore') .'\')',
            'class'     => 'add',
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_tree')->toHtml();
    }

    /**
     * Retrieve buttons
     *
     * @return string
     */
    public function getAddNewButtonHtml()
    {
        return join(' ', array(
            $this->getChildHtml('add_new_website'),
            $this->getChildHtml('add_new_group'),
            $this->getChildHtml('add_new_store')
        ));
    }
}
