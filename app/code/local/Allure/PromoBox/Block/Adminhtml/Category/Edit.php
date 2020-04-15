<?php

class Allure_PromoBox_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'promobox';
        $this->_controller = 'adminhtml_category';
        
        $this->_updateButton('save', 'label', Mage::helper('promobox')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('promobox')->__('Delete'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            } ";
    }

    public function getHeaderText()
    {

        if( Mage::registry('category_data') && Mage::registry('category_data')->getId() ) {
            return Mage::helper('promobox')->__("Edit Promobox Category '%s'", $this->htmlEscape(Mage::helper("promobox")->getCategoryName(Mage::registry('category_data')->getCategoryId())));
        } else {
            return Mage::helper('promobox')->__('Add Promobox Category');
        }
    }

}