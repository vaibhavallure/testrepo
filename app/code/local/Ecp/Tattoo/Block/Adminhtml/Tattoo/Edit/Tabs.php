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
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tattoo
 *
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tattoo_Block_Adminhtml_Tattoo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('tattoo_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ecp_tattoo')->__('Tattoo Artist Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ecp_tattoo')->__('Artist Details'),
          'title'     => Mage::helper('ecp_tattoo')->__('Artist Details'),
          'content'   => $this->getLayout()->createBlock('ecp_tattoo/adminhtml_tattoo_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('images_section', array(
          'label'     => Mage::helper('ecp_tattoo')->__('Artist\'s Artworks'),
          'title'     => Mage::helper('ecp_tattoo')->__('Artist\'s Artworks'),
          'content'   => $this->getLayout()->createBlock('ecp_tattoo/adminhtml_catalog_product_helper_form_gallery_content')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}