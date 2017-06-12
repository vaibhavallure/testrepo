<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Adminhtml_Celebrities_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('celebrities_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ecp_celebrities')->__('Celebrity Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ecp_celebrities')->__('Celebrity Information'),
          'title'     => Mage::helper('ecp_celebrities')->__('Celebrity Information'),
          'content'   => $this->getLayout()->createBlock('ecp_celebrities/adminhtml_celebrities_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}