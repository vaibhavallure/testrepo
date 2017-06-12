<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Adminhtml_Celebrities_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_celebrities';
        $this->_controller = 'adminhtml_celebrities';
        
        $this->_updateButton('save', 'label', Mage::helper('ecp_celebrities')->__('Save Celebrity'));
        $this->_updateButton('delete', 'label', Mage::helper('ecp_celebrities')->__('Delete Celebrity'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('celebrities_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'celebrities_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'celebrities_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('celebrities_data') && Mage::registry('celebrities_data')->getId() ) {
            return Mage::helper('ecp_celebrities')->__("Edit celebrity '%s'", $this->htmlEscape(Mage::registry('celebrities_data')->getCelebrityName()));
        } else {
            return Mage::helper('ecp_celebrities')->__('Add Celebrity');
        }
    }
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}