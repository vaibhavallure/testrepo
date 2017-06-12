<?php
class Ecp_Familycolors_Block_Adminhtml_Familycolors_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'familycolors_adminhtml';
        $this->_controller = 'familycolors';
 
        $this->_updateButton('save', 'label', Mage::helper('familycolors')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('familycolors')->__('Delete Item'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('familycolors_data') && Mage::registry('familycolors_data')->getId() ) {
            return Mage::helper('familycolors')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('familycolors_data')->getName()));
        } else {
            return Mage::helper('familycolors')->__('Add Item');
        }
    }

    protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        parent::_prepareLayout();
    }
}