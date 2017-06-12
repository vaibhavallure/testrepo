<?php
class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_restrictcustomergroup';
    $this->_blockGroup = 'restrictcustomergroup';
    $this->_headerText = Mage::helper('restrictcustomergroup')->__('Rules Manager');
    $this->_addButtonLabel = Mage::helper('restrictcustomergroup')->__('New Rule');
    parent::__construct();
  }
}