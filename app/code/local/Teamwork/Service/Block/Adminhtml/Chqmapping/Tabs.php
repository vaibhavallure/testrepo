<?php

class Teamwork_Service_Block_Adminhtml_Chqmapping_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{   
    public function __construct()
    {
        parent::__construct();
        $this->setTitle($this->__('Information'));
        
    }

    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => 'General',
            'active'    => true,
            'title' => 'general',
         ));
        
        return parent::_prepareLayout();
    }
}
