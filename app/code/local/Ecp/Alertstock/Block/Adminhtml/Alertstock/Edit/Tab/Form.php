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
 * @package     Ecp_Alertstock
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Alertstock
 *
 * @category    Ecp
 * @package     Ecp_Alertstock
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Alertstock_Block_Adminhtml_Alertstock_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('alertstock_form', array('legend'=>Mage::helper('ecp_alertstock')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('ecp_alertstock')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('ecp_alertstock')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_alertstock')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_alertstock')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_alertstock')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('ecp_alertstock')->__('Content'),
          'title'     => Mage::helper('ecp_alertstock')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getAlertstockData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getAlertstockData());
          Mage::getSingleton('adminhtml/session')->setAlertstockData(null);
      } elseif ( Mage::registry('alertstock_data') ) {
          $form->setValues(Mage::registry('alertstock_data')->getData());
      }
      return parent::_prepareForm();
  }
}