<?php
class Ecp_Familycolors_Block_Adminhtml_Familycolors_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('familycolors_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('familycolors')->__('Style Management'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('familycolors')->__('Edit'),
            'title'     => Mage::helper('familycolors')->__('Edit'),
            'content'   => $this->getLayout()->createBlock('familycolors_adminhtml/familycolors_edit_tab_form')->toHtml(),
        ));
       
        return parent::_beforeToHtml();
    }
}