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
class Ecp_Tattoo_Block_Adminhtml_Tattoo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('tattoo_form', array('legend'=>Mage::helper('ecp_tattoo')->__('Celebrity information')));

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Email'),
			'required'  => true,
            'name'      => 'email',
            'class'     => 'validate-email',
        ));
        
        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Image'),
            'name'      => 'image',
            'after_element_html' => '<p class="after-element-html">' . Mage::helper('ecp_tattoo')->__('Please provide only images with aspect ration 4:3') . '</p>'
        ));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
     
        $wysiwygConfig->setDirectivesUrl(str_replace('ecptattoo','admin',$wysiwygConfig->getDirectivesUrl()));
        $plugins = $wysiwygConfig->getPlugins();
        $plugins[0]['options']['onclick']['subject'] = str_replace('ecptattoo','admin',$plugins[0]['options']['onclick']['subject']);
        $plugins[0]['options']['url'] = str_replace('ecptattoo','admin',$plugins[0]['options']['url']);
        $wysiwygConfig->setPlugins($plugins);
        $wysiwygConfig->setDirectivesUrlQuoted(str_replace('ecptattoo','admin',$wysiwygConfig->getDirectivesUrlQuoted()));
        $wysiwygConfig->setFilesBrowserWindowUrl(str_replace('ecptattoo','admin',$wysiwygConfig->getFilesBrowserWindowUrl()));
        $wysiwygConfig->setWidgetWindowUrl(str_replace('ecptattoo','admin',$wysiwygConfig->getWidgetWindowUrl()));
      
        $fieldset->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('ecp_tattoo')->__('Description'),
            'title'     => Mage::helper('ecp_tattoo')->__('Description'),
			'required'  => true,
            'style'     => 'height:26em;width:60em;',            
            'wysiwyg'   => true,
            'config'    => $wysiwygConfig
        ));
        
        $fieldset->addField('url', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('URL'),
            'name'      => 'url',
        ));
        
        $tmp = array();
		$tmp[]=array(
        	'label' => "Please Select a block",
        	'value' => ""		
		);
        foreach(Mage::getModel('cms/block')->getCollection() as $block){
            $tmp[] = array(
                'label' => $block->getTitle(),
                'value' => $block->getBlockId()
            );
        }

        $fieldset->addField('banner_gift','select',array(
            'label' => Mage::helper('ecp_tattoo')->__('Banner Gift Card'),
            'name' => 'banner_gift',
            'values' =>$tmp 
        ));
        
        $fieldset->addField('hours','select',array(
            'label' => Mage::helper('ecp_tattoo')->__('Hour & Location'),
            'name' => 'hours',
            'required'  => true,
            'values' =>$tmp 
        ));
		
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                                array(
                                    'value'     => 1,
                                    'label'     => Mage::helper('ecp_tattoo')->__('Enabled'),
                                ),
                                array(
                                    'value'     => 2,
                                    'label'     => Mage::helper('ecp_tattoo')->__('Disabled'),
                                ),
                            ),
        ));     
     
      if ( Mage::getSingleton('adminhtml/session')->getCelebritiesData() )
      {
          $form->setValues();
          Mage::getSingleton('adminhtml/session')->setCelebritiesData(null);
      } elseif ( Mage::registry('tattoo_data') ) {
          $data = Mage::registry('tattoo_data');
          //if($data->getImage())
              //$data->setImage('tattoo'.DS.$data->getImage());
          $form->setValues($data->getData());
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
