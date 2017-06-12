<?php
/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Searchbox
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Searchbox
 *
 * @category    Ecp
 * @package     Ecp_Searchbox
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Searchbox_Block_Adminhtml_Searchbox extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_searchbox';
    $this->_blockGroup = 'ecp_searchbox';
    $this->_headerText = Mage::helper('ecp_searchbox')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('ecp_searchbox')->__('Add Item');
    parent::__construct();
  }
}