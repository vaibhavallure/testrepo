<?php
class Ecp_Footlinks_Block_Adminhtml_Footlinks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    parent::__construct();
    $this->_controller = 'adminhtml_footlinks';
    $this->_blockGroup = 'footlinks';
    $this->_headerText = Mage::helper('footlinks')->__('Footlinks Manager');   
    $this->_updateButton('add', 'label', Mage::helper('footlinks')->__('Add link'));
    
    $this->_addButton('sort',array(
                  'label' => Mage::helper('adminhtml')->__('Sort order'),
                  'onclick' => 'setLocation(\'' . $this->getUrl('*/*/sort') .'\')',
                  'class' => 'add',
        ), -100);
  }
}