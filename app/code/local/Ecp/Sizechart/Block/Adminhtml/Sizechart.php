<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Adminhtml_Sizechart extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_sizechart';
    $this->_blockGroup = 'ecp_sizechart';
    $this->_headerText = Mage::helper('ecp_sizechart')->__('Size chart Manager');
    $this->_addButtonLabel = Mage::helper('ecp_sizechart')->__('Add Size chart');
    parent::__construct();
  }
}