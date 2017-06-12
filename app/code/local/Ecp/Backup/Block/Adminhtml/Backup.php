<?php

class Entrepids_Backup_Block_Adminhtml_Backup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_backup';
    $this->_blockGroup = 'entrepids_backup';
    $this->_headerText = Mage::helper('entrepids_backup')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('entrepids_backup')->__('Add Item');
    parent::__construct();
  }
}