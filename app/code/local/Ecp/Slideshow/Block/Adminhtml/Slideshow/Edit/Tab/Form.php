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
 * @package     Ecp_Slideshow
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Slideshow_Block_Adminhtml_Slideshow_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('slideshow_form', array('legend'=>Mage::helper('ecp_slideshow')->__('Item information')));
     
      $fieldset->addField('slide_background', 'image', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Background'),
          'required'  => false,
          'name'      => 'slide_background',
      ));
      
      $fieldset->addField('slide_thumb', 'image', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Thumbnail'),
          'required'  => false,
          'name'      => 'slide_thumb',
      ));
      
      $fieldset->addField('url', 'text', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Url'),
          'required'  => false,
          'name'      => 'url',
      ));
	  $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Position'),
          'required'  => false,
          'name'      => 'position',
      ));

        $fieldset->addField('slide_content', 'textarea', array(
        'label'     => Mage::helper('ecp_slideshow')->__('Content'),
        'required'  => false,
        'name'      => 'slide_content',
        ));
      $fieldset->addField(addslashes('background'), 'select', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Background'), 
          'value'  => '1',
          'values' => array('0'=>'Yes','1' => 'No'), 
            'name' => 'background',
          'after_element_html' => '<small>Background for HTML Content</small>', 
        ));


    $fieldset->addField('switch', 'select', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Switch Caption Mode'),
          'name'      => 'switch',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_slideshow')->__('HTML'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('ecp_slideshow')->__('Thumbnail'),
              ),
          ),
      ));
		      
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_slideshow')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_slideshow')->__('Enabled'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('ecp_slideshow')->__('Disabled'),
              ),
          ),
      ));
     

      if ( Mage::getSingleton('adminhtml/session')->getMenuData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMenuData());
          Mage::getSingleton('adminhtml/session')->setMenuData(null);
      } elseif ( Mage::registry('slideshow_data') ) {
          $path = Mage::getBaseUrl('media').'slideshow/';
          $slide = Mage::registry('slideshow_data');
          
          $tmp = $slide->getSlideThumb();
          if(is_array($tmp)) $slide->setSlideThumb($path.basename($tmp['value']));
          elseif(!empty($tmp)) $slide->setSlideThumb($path.basename($slide->getSlideThumb()));
                  
          $tmp = $slide->getSlideBackground();
          if(is_array($tmp)) $slide->setSlideBackground($path.basename($tmp['value']));
          elseif(!empty($tmp)) $slide->setSlideBackground($path.basename($slide->getSlideBackground()));
          
          $form->setValues($slide->getData());
      }
      return parent::_prepareForm();
  }
}
