<?php

class Allure_PromoBox_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_category';
    $this->_blockGroup = 'promobox';
    $this->_headerText = Mage::helper('promobox')->__('Promobox Category Manager');
    $this->_addButtonLabel = Mage::helper('promobox')->__('Add Promobox Category');
    parent::__construct();
  }
}