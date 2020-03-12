<?php

class Allure_PromoBox_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banner';
    $this->_blockGroup = 'promobox';
    $this->_headerText = Mage::helper('promobox')->__('Banner Manager');
    $this->_addButtonLabel = Mage::helper('promobox')->__('Add Banner');
    parent::__construct();
  }
}