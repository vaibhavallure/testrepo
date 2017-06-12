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
 * @package     Ecp_Video
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Video
 *
 * @category    Ecp
 * @package     Ecp_Video
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Video_Block_Adminhtml_Video_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('video_form', array('legend'=>Mage::helper('ecp_video')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('ecp_video')->__('Title:'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('duration', 'text', array(
          'label'     => Mage::helper('ecp_video')->__('Duration:'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'duration',
      ));

      $fieldset->addField('thumbnail', 'image', array(
          'label'     => Mage::helper('ecp_video')->__('Thumbnail:'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'thumbnail',
      ));

      $fieldset->addField('url', 'text', array(
          'label'     => Mage::helper('ecp_video')->__('URL to Video:'),
          'required'  => false,
          'name'      => 'url',
      ));

      $fieldset->addField('video', 'file', array(
          'label'     => Mage::helper('ecp_video')->__('File:'),
          'required'  => false,
          'name'      => 'video',
	    ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_press')->__('Status:'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_press')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_press')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('ecp_video')->__('Description:'),
          'title'     => Mage::helper('ecp_video')->__('Description'),
          'style'     => 'width:272px; height:70px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getVideoData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getVideoData());
          Mage::getSingleton('adminhtml/session')->setVideoData(null);
      } elseif ( Mage::registry('video_data') ) {
          $form->setValues(Mage::registry('video_data')->getData());
      }
      return parent::_prepareForm();
  }
}