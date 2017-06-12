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
 * @package     Ecp_Reviews
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Reviews
 *
 * @category    Ecp
 * @package     Ecp_Reviews
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Reviews_Block_Adminhtml_Reviews_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('reviews_form', array('legend'=>Mage::helper('ecp_reviews')->__('Item information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('ecp_reviews')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));
      
      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('ecp_reviews')->__('Email'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
      ));
      
       $fieldset->addField('review', 'textarea', array(
          'label'     => Mage::helper('ecp_reviews')->__('Review'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'review',
      ));

     
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_reviews')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_reviews')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_reviews')->__('Pending'),
              ),

              array(
                  'value'     => 3,
                  'label'     => Mage::helper('ecp_reviews')->__('Disabled'),
              )
          ),
      ));
     
     
     
      if ( Mage::getSingleton('adminhtml/session')->getReviewsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getReviewsData());
          Mage::getSingleton('adminhtml/session')->setReviewsData(null);
      } elseif ( Mage::registry('reviews_data') ) {
          $form->setValues(Mage::registry('reviews_data')->getData());
      }
      return parent::_prepareForm();
  }
}