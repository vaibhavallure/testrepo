<?php
/**
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Block_Adminhtml_Seo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_seo';
        $this->_controller = 'adminhtml_seo';
        
        $this->_updateButton('save', 'label', Mage::helper('ecp_seo')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('ecp_seo')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('seo_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'seo_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'seo_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('seo_data') && Mage::registry('seo_data')->getId() ) {
            return Mage::helper('ecp_seo')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('seo_data')->getTitle()));
        } else {
            return Mage::helper('ecp_seo')->__('Add Item');
        }
    }
}