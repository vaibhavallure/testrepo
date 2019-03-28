<?php

class Teamwork_Service_Block_Adminhtml_Chqmapping_New_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{   
    public function __construct()
    {
        parent::__construct();
        $this->setTitle($this->__('First information'));
        
    }

    protected function _prepareLayout()
    {
        $this->addTab('settings', array(
            'label'     => 'Settings',
            'active'    => true,
            'title' => 'Settings',
         ));
        
        return parent::_prepareLayout();
    }
}
