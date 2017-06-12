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
 * @package     Ecp_Faq
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Faq
 *
 * @category    Ecp
 * @package     Ecp_Faq
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Faq_Block_Adminhtml_Faq_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('faq_form', array('legend'=>Mage::helper('ecp_faq')->__('Item information')));
     
      $fieldset->addField('question', 'text', array(
          'label'     => Mage::helper('ecp_faq')->__('Question'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'question',
          'style'     => 'width:700px;',
      ));

      $fieldset->addField('answer', 'textarea', array(
          'label'     => Mage::helper('ecp_faq')->__('Answer'),
          'required'  => false,
          'name'      => 'answer',
          'style'     => 'width:700px; height:200px;',
     ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_faq')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_faq')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_faq')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getFaqData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFaqData());
          Mage::getSingleton('adminhtml/session')->setFaqData(null);
      } elseif ( Mage::registry('faq_data') ) {
          $form->setValues(Mage::registry('faq_data')->getData());
      }
      return parent::_prepareForm();
  }
}