<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Adminhtml_Sizechart_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_sizechart';
        $this->_controller = 'adminhtml_sizechart';
        
        $this->_updateButton('save', 'label', Mage::helper('ecp_sizechart')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('ecp_sizechart')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('sizechart_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sizechart_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sizechart_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('sizechart_data') && Mage::registry('sizechart_data')->getId() ) {
            return Mage::helper('ecp_sizechart')->__("Edit Size chart '%s'", $this->htmlEscape(Mage::registry('sizechart_data')->getTitle()));
        } else {
            return Mage::helper('ecp_sizechart')->__('Add Size chart');
        }
    }
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}