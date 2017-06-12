<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('footlinks_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('footlinks')->__('New Link'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('footlinks')->__('Link Information'),
          'title'     => Mage::helper('footlinks')->__('Link Information'),
          'content'   => $this->getLayout()->createBlock('footlinks/adminhtml_footlinks_edit_tab_form')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}