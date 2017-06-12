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
class Ecp_Seo_Block_Adminhtml_Seo extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_seo';
    $this->_blockGroup = 'ecp_seo';
    $this->_headerText = Mage::helper('ecp_seo')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('ecp_seo')->__('Add Item');
    parent::__construct();
  }
}