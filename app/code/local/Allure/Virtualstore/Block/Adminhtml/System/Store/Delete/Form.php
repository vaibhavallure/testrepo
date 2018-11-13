<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Delete_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('store_delete_form');
        $this->setTitle(Mage::helper('cms')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $dataObject = $this->getDataObject();

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $form->setHtmlIdPrefix('store_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('core')->__('Backup Options'), 'class' => 'fieldset-wide'));

        $fieldset->addField('item_id', 'hidden', array(
            'name'  => 'item_id',
            'value' => $dataObject->getId(),
        ));

        $fieldset->addField('create_backup', 'select', array(
            'label'     => Mage::helper('adminhtml')->__('Create DB Backup'),
            'title'     => Mage::helper('adminhtml')->__('Create DB Backup'),
            'name'      => 'create_backup',
            'options'   => array(
                '1' => Mage::helper('adminhtml')->__('Yes'),
                '0' => Mage::helper('adminhtml')->__('No'),
            ),
            'value'     => '1',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
