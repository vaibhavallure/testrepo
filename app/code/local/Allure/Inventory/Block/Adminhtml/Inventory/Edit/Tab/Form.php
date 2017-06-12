<?php

class Allure_Inventory_Block_Adminhtml_Inventory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        if (Mage::getSingleton('adminhtml/session')->getInventoryData()) {
            $data = Mage::getSingleton('adminhtml/session')->getInventoryData();
            Mage::getSingleton('adminhtml/session')->setInventoryData(null);
        } elseif (Mage::registry('inventory_data')) {
            $data = Mage::registry('inventory_data')->getData();
        }
        $fieldset = $form->addFieldset('inventory_form', array(
            'legend'=>Mage::helper('inventory')->__('Item information')
        ));

        $fieldset->addField('title', 'text', array(
            'label'        => Mage::helper('inventory')->__('Title'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'title',
        ));

        $fieldset->addField('filename', 'file', array(
            'label'        => Mage::helper('inventory')->__('File'),
            'required'    => false,
            'name'        => 'filename',
        ));

        $fieldset->addField('status', 'select', array(
            'label'        => Mage::helper('inventory')->__('Status'),
            'name'        => 'status',
            'values'    => Mage::getSingleton('inventory/status')->getOptionHash(),
        ));

        $fieldset->addField('content', 'editor', array(
            'name'        => 'content',
            'label'        => Mage::helper('inventory')->__('Content'),
            'title'        => Mage::helper('inventory')->__('Content'),
            'style'        => 'width:700px; height:500px;',
            'wysiwyg'    => false,
            'required'    => true,
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}
