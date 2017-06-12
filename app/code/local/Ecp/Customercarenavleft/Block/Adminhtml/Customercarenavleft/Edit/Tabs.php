<?php
/**
 * Entrepids
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
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Customercarenavleft
 *
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Customercarenavleft_Block_Adminhtml_Customercarenavleft_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('customercarenavleft_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ecp_customercarenavleft')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ecp_customercarenavleft')->__('Item Information'),
          'title'     => Mage::helper('ecp_customercarenavleft')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('ecp_customercarenavleft/adminhtml_customercarenavleft_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}