<?php

class Entrepids_Backup_Block_Adminhtml_Backup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('backup_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('entrepids_backup')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('entrepids_backup')->__('Item Information'),
          'title'     => Mage::helper('entrepids_backup')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('entrepids_backup/adminhtml_backup_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}