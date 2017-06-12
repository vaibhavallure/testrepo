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
 * @package     Ecp_Shippingtext
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Shippingtext
 *
 * @category    Ecp
 * @package     Ecp_Shippingtext
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Shippingtext_Block_Adminhtml_Shippingtext_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shippingtext_form', array('legend'=>Mage::helper('ecp_shippingtext')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('ecp_shippingtext')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('ecp_shippingtext')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));*/
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_shippingtext')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_shippingtext')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_shippingtext')->__('Disabled'),
              ),
          ),
      ));
      
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
      
      $wysiwygConfig->setDirectivesUrl(str_replace('ecpshippingtext','admin',$wysiwygConfig->getDirectivesUrl()));
      $plugins = $wysiwygConfig->getPlugins();
      $plugins[0]['options']['onclick']['subject'] = str_replace('ecpshippingtext','admin',$plugins[0]['options']['onclick']['subject']);
      $plugins[0]['options']['url'] = str_replace('ecpshippingtext','admin',$plugins[0]['options']['url']);
      $wysiwygConfig->setPlugins($plugins);
      $wysiwygConfig->setDirectivesUrlQuoted(str_replace('ecpshippingtext','admin',$wysiwygConfig->getDirectivesUrlQuoted()));
      $wysiwygConfig->setFilesBrowserWindowUrl(str_replace('ecpshippingtext','admin',$wysiwygConfig->getFilesBrowserWindowUrl()));
      $wysiwygConfig->setWidgetWindowUrl(str_replace('ecpshippingtext','admin',$wysiwygConfig->getWidgetWindowUrl()));
      
      $content = $fieldset->addField('block_content', 'editor', array(
          'name'      => 'block_content',
          'label'     => Mage::helper('ecp_shippingtext')->__('Content'),
          'title'     => Mage::helper('ecp_shippingtext')->__('Content'),
          'style'     => 'height:26em;width:60em;',
          'wysiwyg'   => true,
          'required'  => true,
          'config' => $wysiwygConfig,
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getShippingtextData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShippingtextData());
          Mage::getSingleton('adminhtml/session')->setShippingtextData(null);
      } elseif ( Mage::registry('shippingtext_data') ) {
          $form->setValues(Mage::registry('shippingtext_data')->getData());
      }
      return parent::_prepareForm();
  }
  
  protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}