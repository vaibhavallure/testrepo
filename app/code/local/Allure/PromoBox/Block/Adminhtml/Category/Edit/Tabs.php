<?php

class Allure_PromoBox_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('category_tab');
      $this->setDestElementId('edit_form');
  }
  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('promobox')->__('Promobox Category Information'),
          'title'     => Mage::helper('promobox')->__('Promobox Category Information'),
          'content'   => $this->getLayout()->createBlock('promobox/adminhtml_Category_edit_tab_form')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}