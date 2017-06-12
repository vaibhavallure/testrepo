<?php
/**
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Block_Adminhtml_Seo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('seo_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ecp_seo')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ecp_seo')->__('Item Information'),
          'title'     => Mage::helper('ecp_seo')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('ecp_seo/adminhtml_seo_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}