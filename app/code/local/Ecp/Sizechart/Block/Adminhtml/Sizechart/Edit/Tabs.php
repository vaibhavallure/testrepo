<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Adminhtml_Sizechart_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('sizechart_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ecp_sizechart')->__('Size chart Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ecp_sizechart')->__('Size chart Information'),
          'title'     => Mage::helper('ecp_sizechart')->__('Size chart Information'),
          'content'   => $this->getLayout()->createBlock('ecp_sizechart/adminhtml_sizechart_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}