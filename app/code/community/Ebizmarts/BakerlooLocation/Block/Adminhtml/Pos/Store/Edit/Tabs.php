<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('bakerloolocation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bakerloo_location')->__('Location information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            array(
            'label'     => Mage::helper('bakerloo_location')->__('Location information'),
            'title'     => Mage::helper('bakerloo_location')->__('Location information'),
            'content'   => $this->getLayout()->createBlock('bakerloo_location/adminhtml_pos_store_edit_tab_main')->toHtml(),
            'active'    => true
            )
        );

        return parent::_beforeToHtml();
    }
}
